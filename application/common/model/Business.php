<?php

namespace app\common\model;


class Business extends Cosmetic
{
    use \traits\model\SoftDelete;

    // 表名
    protected $name = 'business';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public $keywordsFields = ["idcode"];

    protected static function init()
    {
        parent::init();
        $upstat = function($row){
            $quantity = self::where("branch_model_id", $row->branch_model_id)->count();
            $where = ["table"=>"business","field"=>'quantity',"branch_model_id"=>$row->branch_model_id];
            $stat = new Statistics();
            if (($ns = $stat->where($where)->find()) == null) {
                $stat->data($where, true);
            } else $stat = $ns;
            $stat->save(['value' => $quantity]);
        };
        self::afterInsert($upstat); self::afterDelete($upstat);

        self::beforeInsert(function($row){
            $row['surplus_price'] = $row['sum_settle_price'];
        });

        self::beforeUpdate(function($row){
            if ($row['settle_state'] == 918) {
                $row['surplus_price'] = 0;
            } else {
                $row['surplus_price'] = $row['sum_settle_price'] - $row['deficit_price'];
            }
            if ($row['settle_state'] < 917) {
                if ($row['sum_settle_price'] > 0 && $row['deficit_price'] <=0) {
                    $row['settle_state'] = 913;
                } else if($row['deficit_price'] > 0 && $row['deficit_price'] < $row['sum_settle_price']) {
                    $row['settle_state'] = 914;
                } else if ($row['deficit_price'] >= $row['sum_settle_price']) {
                    $row['settle_state'] = 916;
                }
            }
        });

        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("BUS%06d", $maxid);
        });
    }

    public function settle() {
        $db     = $this->getQuery();
        $db->startTrans();
        try {
            $this->status ='locked';
            $this->settle_state =918;
            $this->settle_date =time();
            $data = $this->getData();

            if ($result) {
                $result = $this->isUpdate(true)->save();
                if ($result) {
                    $db->commit();
                }
            }
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function calculateDeficit() {
        $where = array(
            'related_type'=>$this->name,
            'related_model_id'=>$this->id,
            'status'=>'locked'
        );
        $out_sum_money = Account::where($where)->where('cheque_model_id',32)->sum("money");
        $in_sum_money = Account::where($where)->where('cheque_model_id',29)->sum("money");
        return max($in_sum_money - $out_sum_money, 0);
    }

    public function calculate() {
        $this->deficit_price = $this->calculateDeficit();
    }

    public function amount(){
        $this->calculate();
        $this->isUpdate(true)->save();
    }

    public function customer() {
        return $this->hasOne('customer','id','customer_model_id')->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->setEagerlyType(0);
    }
}