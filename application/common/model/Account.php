<?php

namespace app\common\model;

use think\Db;
use think\Exception;
use think\Loader;
use think\Request;
use traits\model\SoftDelete;

class Account extends Cosmetic
{
    use SoftDelete;

    // 表名
    protected $name = 'account';
    // 追加属性
    public $keywordsFields = ["idcode"];

    public static function instance($layer = "model") {
        $module = Request::instance()->module();
        $class = Loader::parseClass($module, $layer, "account");
        if (class_exists($class)) {
            $ins = new $class();
        } else {
            $ins = new static();
        }
        return $ins;
    }

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row) {
            if ($row->cheque_model_id == 48) {
                $row['reckon_model_id'] = $row['inflow_model_id'];
                $row['inflow_model_id'] = 0;
            }
        });
        $prepareModelField = function ($row) {
            $cheque = model("cheque")->get($row['cheque_model_id']);
            if (!$cheque)
                throw new Exception('无效的类目类型', 100006);

            if (!isset($row['reckon_type']) && $cheque["reckon_table"]) {
                $row["reckon_type"] = $cheque["reckon_table"];
            }
            if ($row['reckon_type']) {
                if ($row['reckon_type'] == "flow") {
                    $reckon = model($cheque['inflow_table']);
                    if ($reckon) {
                        $reckon->where(array("id" => $row['inflow_model_id']));
                    }
                }elseif ($row['reckon_type'] == "payconfirm"){
                    $reckon = model($cheque['inflow_table']);
                    if ($reckon) {
                        $reckon->where(array("id" => $row['reckon_model_id']));
                    }
                } else {
                    $reckon = model($row['reckon_type']);
                    if ($reckon) {
                        $reckon->where(array("id" => $row['reckon_model_id']));
                    }
                }
                if (!$reckon) {
                    throw new Exception('无效的类目类型', 100006);
                }
                if (isset($reckon->keywordsFields)) {
                    $reckon->field($reckon->keywordsFields);
                }

                if ($row['reckon_type'] != "branch" && !in_array($row['reckon_type'],["flow","bursar"])) {
                    $reckon->field('branch_model_id');
                }

                $data = $reckon->find();
                if ($data) {
                    if (isset($data['branch_model_id'])) {
                        $row["reckon_branch_model_id"] = $data->branch_model_id;
                    }
                    $row["reckon_model_keyword"] = $data->visible(['name', 'idcode'])->toJson();
                }
            }

            if (!isset($row['related_type']) && $cheque["related_table"]) {
                $row["related_type"] = $cheque["related_table"];
            }
            if (isset($row['related_model_id']) && isset($row['related_type']) && $row['related_type']) {
                $related = model($row['related_type']);
                if ($related) {
                    $related->visible(['name', 'idcode']);
                    $related->where(array("id" => $row['related_model_id']));
                    if (isset($related->keywordsFields)) {
                        $related->field($related->keywordsFields);
                    }
                    $data = $related->find();
                    if ($data) {
                        $row["related_model_keyword"] = $data->toJson();
                    }
                }
            }

            if (!isset($row['inflow_type']) && $cheque["inflow_table"]) {
                $row["inflow_type"] = $cheque["inflow_table"];
            }
            if (isset($row['inflow_model_id']) && isset($row['inflow_type']) && $row['inflow_type']) {
                $inflow = model($row['inflow_type']);
                if ($inflow) {
                    $inflow->visible(['name', 'idcode']);
                    $inflow->where(array("id" => $row['inflow_model_id']));
                    if (isset($inflow->keywordsFields)) {
                        $inflow->field($inflow->keywordsFields);
                    }
                    $data = $inflow->find();
                    if ($data) {
                        $row["inflow_model_keyword"] = $data->toJson();
                    }
                }
            }
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("%s%06d", date("YmdHis"), $maxid);
            $row['paymenttime'] = time();
        };
        self::beforeInsert($prepareModelField);

        self::beforeDelete(function ($row) {
            return $row['status'] != "locked";
        });

        $statcal = function($where, $amount){
            $stat = new Statistics();
            if (($ns = $stat->where($where)->find()) == null) {
                $stat->data($where, true);
            } else $stat = $ns;
            $stat->save(['value' => $amount]);
        };
        self::afterInsert(function($row) use($statcal){
            $where = [
                'table'=>"account",
                'field'=>$row->cheque_model_id,
            ];
            if (isset($row['reckon_branch_model_id'])) {
                $where['branch_model_id'] = $row['reckon_branch_model_id'];
            }
            $amount = model("account")->where(function($query)use($row){
                $query->where('cheque_model_id',$row->cheque_model_id);
                if (isset($row['reckon_branch_model_id'])) {
                    $query->where('reckon_branch_model_id',$row->reckon_branch_model_id);
                }
            })->sum("money");

            $statcal($where, $amount);
        });

        self::afterInsert(function($row) use($statcal){
            $where = [
                'table' => "account",
                'field' => $row->reckon_type . "_" . $row->cheque->mold,
            ];

            if (isset($row['reckon_branch_model_id'])) {
                $where['branch_model_id'] = $row['reckon_branch_model_id'];
            }
            $amount = model("account")->with("cheque")->where(['cheque.mold' => $row->cheque->mold, 'reckon_type' => $row->reckon_type])->sum("money");
            $statcal($where, $amount);
        });

        self::afterInsert(function($row) use($statcal){
            $where = [
                'table' => "account",
                'field' => $row->reckon_type . "_balance",
            ];
            if (isset($row['reckon_branch_model_id'])) {
                $where['branch_model_id'] = $row['reckon_branch_model_id'];
            }

            $sql = sprintf("SELECT (SELECT SUM(`value`) FROM fa_statistics WHERE `field`='%s_1') - (SELECT SUM(`value`) FROM fa_statistics WHERE `field`='%s_-1') AS s", $row->reckon_type, $row->reckon_type);
            $result = Db::query($sql);
            if ($result && count($result) > 0) {
                $statcal($where, $result[0]['s']);
            }
        });

        self::afterUpdate(function($row){
            $cd = $row->getChangedData();
            if (isset($cd['voucher']) && isset($row['infow_account_id']) && $row['infow_account_id']) {
                self::update(['voucher' => $row['voucher'],'id'=>$row['infow_account_id']]);
            }

            if (isset($cd['weshow'])) {
                $reckon = model($row['reckon_type']);
                if (isset($row['reckon_model_id']) && $row['reckon_model_id'] !== null && $row['reckon_model_id'] !== '') {
                    $reckon = $reckon->get($row['reckon_model_id']);
                }
                $reckon->amount();
            }
        });
    }

    public function add($data = []) {
        if ($this->validate) {
            $validate = $this->validate;
            if (!$this->validateData($data, $validate)) {
                return false;
            }
        }

        $db     = $this->getQuery();
        $db->startTrans();
        try {
            $result = $this->isUpdate(false)->allowField(true)->save($data);
            if ($result) {
                $reckonId = $this->id;
                $reckon = model($this->reckon_type);
                if (isset($this['reckon_model_id']) && $this['reckon_model_id'] !== null && $this['reckon_model_id'] !== '') {
                    $reckon = $reckon->get($this->reckon_model_id);
                }
                $reckon->amount();

                $cheque = model("cheque")->get($this->cheque_model_id,[],true);
                if ($cheque && $cheque['inflow_cheque_id']) {
                    $data['infow_account_id'] = $reckonId;
                    if ($cheque['inflow_table']) {
                        $data['reckon_type'] = $cheque['inflow_table'];
                    }
                    $data['cheque_model_id'] = $cheque['inflow_cheque_id'];
                    swap_var($data['reckon_model_id'],$data['inflow_model_id']);

                    $result = self::instance()->create($data, true);
                    if ($result) {
                        $this->save(['infow_account_id'=>$result->id]);
                        $reckon = model($result['reckon_type']);
                        if (isset($result['reckon_model_id']) && $result['reckon_model_id'] !== null && $result['reckon_model_id'] !== '') {
                            $reckon = $reckon->get($result['reckon_model_id']);
                        }
                        $reckon->amount();
                    }
                }
                if ($cheque["related_table"] && $this->related_model_id) {
                    model($cheque['related_table'])->where("id", $this->related_model_id)->find()->amount();
                }
            }
            if ($result) {
                $db->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function addextra($money) {
        $data = [
            "cheque_model_id"=>33,
            "money"=>$money,
            "creator_model_id"=>$this->creator_model_id,
            "reckon_type"=>$this->reckon_type,
            "reckon_model_id"=>$this->reckon_model_id,
            "payway"=>14,
            "paymenttime"=>$this->paymenttime,
            "status"=>"locked",
            "description"=>$this->description,
        ];
        $result = self::instance()->create($data, true);
        if ($result) {
            model($result['reckon_type'])->get($result['reckon_model_id'])->amount();
        }
        return $result;
    }

    public function getNameAttr($value, $data) {
        return $data['idcode'];
    }

    public function business() {
        return $this->hasOne('business','id','related_model_id')->setEagerlyType(0)->joinType("LEFT");
    }
    public function provider() {
        return $this->hasOne('provider','id','related_model_id')->setEagerlyType(0)->joinType("LEFT");
    }

    public function course() {
        return $this->hasOne('course','id','related_model_id')->setEagerlyType(0)->joinType("LEFT");
    }

    public function uncertain() {
        return $this->hasOne('uncertain','id','related_model_id')->setEagerlyType(0)->joinType("LEFT");
    }

    public function cheque() {
        return $this->hasOne('cheque','id','cheque_model_id')->setEagerlyType(0);
    }

    public function customer() {
        return $this->hasOne('customer','id','reckon_model_id')->setEagerlyType(0)->joinType("LEFT");
    }

    public function staff() {
        return $this->hasOne('staff','id','reckon_model_id')->setEagerlyType(0)->joinType("LEFT");
    }

    public function branch() {
        return $this->hasOne('branch','id','reckon_model_id')->setEagerlyType(0)->joinType("LEFT");
    }
}
