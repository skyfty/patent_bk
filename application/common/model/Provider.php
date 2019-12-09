<?php

namespace app\common\model;

use think\Db;
use think\Hook;
use Endroid\QrCode\QrCode;

class Provider extends Cosmetic
{
    use \traits\model\SoftDelete;

    protected $name = 'provider';

    public $keywordsFields = ["idcode"];

    public function getSelectField($name, $value) {
        $list= Fields::get(['name'=>$name,'model_table'=>$this->name],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStateTextAttr($value, $data) {
        $value = $value ? $value : $data['state'];
        return $this->getSelectField('state', $value);
    }

    public function getCheckworkTextAttr($value, $data) {
        $value = $value ? $value : $data['checkwork'];
        return $this->getSelectField('checkwork', $value);
    }

    public function getEvaluateStateTextAttr($value, $data) {
        $value = $value ? $value : $data['evaluate_state'];
        return $this->getSelectField('evaluate_state', $value);
    }

    protected static function upstat($row) {
        $quantity = self::where(['branch_model_id'=>$row->branch_model_id])->count();
        $where = ["table"=>"provider","field"=>'quantity', 'branch_model_id'=>$row->branch_model_id];
        $stat = new Statistics();
        if (($ns = $stat->where($where)->find()) == null) {
            $stat->data($where, true);
        } else $stat = $ns;
        $stat->save(['value' => $quantity]);
    }

    public static function updateCourse($data) {
        $params = [
            'package_model_id'=>$data['package_model_id'],
            'appoint_promotion_model_id'=>$data['appoint_promotion_model_id'],
            'appoint_course'=>$data['appoint_course'],
            'appoint_time'=>$data['appoint_time'],
            'classroom_model_id'=>$data['classroom_model_id'],
            'staff_model_id'=>$data['staff_model_id'],
        ];
        $customerCount = model("provider")->where($params)->count("id");
        $customerIds = $customerCount > 0 ? implode(",", model("provider")->where($params)->column("customer_model_id")):"";
        $providerIds = $customerCount > 0 ? implode(",", model("provider")->where($params)->column("id")):"";

        $course = model("course")->get($params);
        $params['customer_count'] =$customerCount;
        $params['customer_model_ids'] =$customerIds;
        $params['provider_model_ids'] =$providerIds;
        $params['customer_count'] =$customerCount;
        $params['starttime'] = $data['starttime'];
        $params['endtime'] = $data['endtime'];
        $params['staff_model_id'] = $data['staff_model_id'];
        $params['branch_model_id'] = $data['branch_model_id'];
        $params['package_model_id'] = $data['package_model_id'];
        $params['period_model_id'] = $data['period_model_id'];

        if (!$course && $customerCount > 0) {
            model("course")->create($params);
        } else if ($customerCount>0) {
            $course->save($params);
        } elseif ($course) {
            $course->delete();
        }
    }

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $row['name'] = $row->promotion->name;
            $row['state'] =1;
        });

        $updateStartEndTime = function($row) {
            $appointCourse = $row->getData('appoint_course');
            $course = explode("-", $appointCourse);
            if (count($course) != 2)
                return;
            $appointTime = $row->getData('appoint_time');
            $appointTime = date("Y-m-d", $appointTime);
            $starttime = $appointTime ." ".$course[0];
            $endtime = $appointTime ." ".$course[1];
            $row['starttime'] = strtotime($starttime);
            $row['endtime'] = strtotime($endtime);
        };
        self::beforeUpdate($updateStartEndTime);self::beforeInsert($updateStartEndTime);

        self::beforeUpdate(function($row){
            if (in_array($row->checkwork, [3,4,0])) {
                $row->evaluate_state = -1;
                $row->evaluatetime = null;
            } elseif ($row->evaluate_lore!=null || $row->evaluate_mentality!=null  || $row->evaluate_order!=null ) {
                if ($row->campaign && $row->record_sheet && $row->achievement) {
                    $row->evaluate_state = 1;
                    $row->evaluatetime = time();
                } else {
                    $row->evaluate_state = 0;
                    $row->evaluatetime = null;
                }
                if ($row['checkwork'] == 2 && $row['state'] == 5) {
                    $amount = $row->evaluate_lore + $row->evaluate_mentality + $row->evaluate_order;
                    $data = [
                        'customer_model_id' => $row['customer_model_id'],
                        'provider_model_id' => $row['id'],
                        'status' => 1,
                        'source' => "evaluate",
                    ];
                    $wisdom = model("wisdom")->where($data)->find();
                    if ($wisdom) {
                        $wisdom->save(['amount' => $amount]);
                    } else {
                        $data['amount'] = $amount;
                        model("wisdom")->create($data);
                    }
                }
            } else {
                $row->evaluate_state = 0;
                $row->evaluatetime = null;
            }
            if ($row->evaluate_state == 1 && $row['state'] == 5) {
                $row->state = 6;
//                $row->settle();
                Hook::listen('evaluate',$row);
            }
        });

        self::afterUpdate(function($row){
            $promotion_amount = self::where(['customer_model_id'=>$row['customer_model_id']])->where('state','in',[5,6])->count();
            model("customer")->get($row['customer_model_id'])->save(['promotion_amount'=>$promotion_amount]);

            if (isset($row['deletetime']) && $row['deletetime']) {
                $obtains = \app\common\model\Genre::getObtainList();
                foreach($obtains as $k=>$o) {
                    model("LoreGenre.CustomerAmount".$k)->where('customer_model_id', $row['customer_model_id'])->delete();
                }
            }
            self::updateCourse($row->getData());
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['appoint_course']) || isset($changeData['appoint_time']) || isset($changeData['classroom_model_id']) || isset($changeData['staff_model_id'])) {
                self::updateCourse($row->origin);
            }
        });

        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("PV%06d", $maxid);
//            $row['emolument'] = $row->staff->emolument;
        });


        $countProvider = function($row) {
            $ids = self::where("customer_model_id", $row['customer_model_id'])->column("id");
            if ($ids) {
                $idcnt = count($ids);
                $ids = implode(",", $ids);
                model("customer")->save(['provider_ids'=>$ids, 'promotion_total'=>$idcnt], ["id"=>$row['customer_model_id']]);
            }
            $ids = self::where("staff_model_id", $row['staff_model_id'])->column("id");
            if ($ids) {
                $ids = implode(",", $ids);
                model("staff")->save(['provider_ids'=>$ids], ["id"=>$row['staff_model_id']]);
            }
            self::upstat($row);
        };
        self::afterInsert($countProvider);self::afterDelete($countProvider);

        $upchangestat = function($row) {
            if ($row['branch_model_id']) {
                $ids = self::where("branch_model_id", $row['branch_model_id'])->column("id");
                model("branch")->where("id", $row['branch_model_id'])->update([
                    'provider_amount' =>count($ids),
                    'provider_ids'=>implode(",", $ids)
                ]);
            }
        };
        self::beforeInsert($upchangestat);self::beforeDelete($upchangestat);

        self::afterDelete(function($row){
            Sellotape::destroy(['provider_model_id'=>$row['id']]);
            self::updateCourse($row->getData());
        });

        self::afterInsert(function($row){
            self::updateCourse($row->getData());
        });
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function customer() {
        return $this->hasOne('customer','id','customer_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function staff() {
        return $this->hasOne('staff','id','staff_model_id')->joinType("LEFT")->field('id,name,idcode,telephone,nickname,emolument')->setEagerlyType(0);
    }

    public function promotion() {
        return $this->hasOne('promotion','id','appoint_promotion_model_id')->joinType("LEFT")->field('id,idcode,name,genre_cascader_id')->setEagerlyType(0);
    }

    public function classroom() {
        return $this->hasOne('classroom','id','classroom_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function package() {
        return $this->hasOne('package','id','package_model_id',[],'left')->field('id,idcode,name')->setEagerlyType(0);
    }

    public function period() {
        return $this->hasOne('period','id','period_model_id',[],'left')->field('id,idcode,name')->setEagerlyType(0);
    }
    public function calculateDeficit() {
        $where = array(
            'related_type'=>$this->name,
            'related_model_id'=>$this->id,
            'status'=>'locked'
        );
        $out_sum_money = Account::where($where)->where('cheque_model_id',17)->sum("money");
        $in_sum_money = Account::where($where)->where('cheque_model_id',8)->sum("money");
        return max($in_sum_money - $out_sum_money, 0);
    }

    //减去课次流水
    protected function decreaseSellotape($amount = 1) {
        model("sellotape")->create([
            'customer_model_id'=>$this['customer_model_id'],
            'branch_model_id'=>$this['branch_model_id'],
            'package_model_id'=>$this['package_model_id'],
            'amount'=>$amount,
            'status'=>-1,
            'provider_model_id'=>$this['id'],
        ]);
    }

    public function countScholarship($lorerange_cascader_id) {
        $obtains = \app\common\model\Genre::getObtainList();
        foreach($obtains as $k=>$o) {
            model("LoreGenre.CustomerAmount".$k)->enumLoreGenre($this->promotion->genre, $lorerange_cascader_id, $this['customer_model_id']);
        }
    }

    public function increaseScholarship() {
        $lores = model("lore")->where("promotion_model_id", $this->promotion->id)->select();
        foreach($lores as $lore) {
            model("scholarship")->create([
                'customer_model_id'=>$this['customer_model_id'],
                'lore_model_id'=>$lore['id'],
                'provider_model_id'=>$this['id'],
            ]);
        }

        $data = [];
        model("LoreLast")->where("customer_model_id",$this['customer_model_id'])->delete();
        foreach($lores as $lore) {
            $data[] = [
                "lorerange_model_id"=>$lore['lorerange_cascader_id'],
                "customer_model_id"=>$this['customer_model_id'],
            ];
        }
        model("LoreLast")->saveAll($data);
    }
    public function countLores() {
        $obtains = \app\common\model\Genre::getObtainList();
        $lores = model("lore")->where("promotion_model_id", $this->promotion->id)->select();
        foreach($lores as $lore) {
            foreach ($obtains as $k => $o) {
                model("LoreGenre.CustomerAmount" . $k)->enumLoreGenre($this->promotion->genre, $lore['lorerange_cascader_id'], $this['customer_model_id']);
            }
        }
    }

    public function signin() {
        if ($this['checkwork'] == 2)
            return true;

        $db     = $this->getQuery();
        $db->startTrans();
        try {
            $data = [
                'customer_model_id'  => $this['customer_model_id'],
                'provider_model_id' => $this['id'],
                'status' =>  0,
            ];
            if ($this['checkwork'] == 4) {
                model("checkwork")->destroy(function($query)use($data){
                    $query->where($data);
                });
            }
            $data['status'] = 1;
            model("checkwork")->create($data);

            $this->checkwork = 2;
            $result = $this->save();
            if ($result) {
                $db->commit();
                Hook::listen('signin',$this);
            }
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function presignin() {
        if (in_array($this['state'],[0, 5, 6]) || in_array($this['checkwork'],[0,2])) {
            return false;
        }
        if ($this['checkwork'] == 1)
            return true;

        $db     = $this->getQuery();
        $db->startTrans();
        try {
            $data = [
                'customer_model_id'  => $this['customer_model_id'],
                'provider_model_id' => $this['id'],
                'status' =>  0,
            ];
            if ($this['checkwork'] == 4) {
                model("checkwork")->destroy(function($query)use($data){
                    $query->where($data);
                });
            }
            $this->checkwork = 1;

            $data = [
                'customer_model_id'  => $this['customer_model_id'],
                'provider_model_id' => $this['id'],
                'status' =>  1,
                'source' => "presignin",
            ];
            $wisdom = model("wisdom")->where($data)->find();
            if ($wisdom == null) {
                $data['amount'] = 2;
                model("wisdom")->create($data);
            }

            $result = $this->save();
            if ($result) {
                $db->commit();
                Hook::listen('presignin',$this);
            }
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function leave() {
        if (in_array($this['state'],[0, 5, 6]) || in_array($this['checkwork'],[0,4])) {
            return false;
        }

        $db     = $this->getQuery();
        $db->startTrans();
        try {
            $data = [
                'customer_model_id'  => $this['customer_model_id'],
                'provider_model_id' => $this['id'],
                'status' =>  1,
            ];
            if ($this['checkwork'] == 2) {
                model("checkwork")->destroy(function($query)use($data){
                    $query->where($data);
                });
            }

            $data['status'] = 0;
            model("checkwork")->create($data);

            $this->checkwork = 4;
            $result = $this->save();
            if ($result) {
                $db->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }


    public function accomplish() {
        if (in_array($this['state'],[5,0, 6])) {
            return false;
        }
        $db     = $this->getQuery();
        $db->startTrans();
        try {
            if ($this['checkwork'] == 2) {
                $this->increaseScholarship();
            }

            if ($this['checkwork'] == 2 || (in_array($this['checkwork'], ['0','4']) && $this->customer->break_appointment_count == 0)) {
                $this->decreaseSellotape();
            }

            $this->state = 5;
            $result = $this->save();
            if ($result) {
                $db->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function recover() {
        $db     = $this->getQuery();
        $db->startTrans();
        try {
            $self = $this;
            foreach(["wisdom","sellotape","checkwork","scholarship"] as $m) {
                model($m)->destroy(function($query)use($self){
                    $query->where('provider_model_id',$self->id);
                });
            }
            $this->countLores();

            $this->state = 1;
            $this->checkwork = 3;
            $this->evaluate_state = -1;
            $this->campaign = $this->record_sheet = "";
            $result = $this->save();
            if ($result) {
                $db->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function getShareUrlAttr($value, $data) {
        $branch = $this->branch;
        $authurl = $branch->authurl;
        $authurl = $authurl?$authurl:\think\Config::get("wechat.authurl");
        return url("provider/evaluate", ['id'=>$this['id']], true, $authurl);
    }

    public function getSharedAttr($value, $data) {
        $branch = $this->branch;
        $authurl = $branch->authurl;
        $authurl = $authurl?$authurl:\think\Config::get("wechat.authurl");
        $imgUrl = \think\Config::get("shared.imgUrl");
        $imgUrl = url($imgUrl, [], false, $authurl);
        $title = $this->customer->nickname . "同学" . $this->promotion->name . "授课报告";
        $shareddesc = $this->customer->nickname."完成了极客思迪的STEAM课程《".$this->promotion->name."》，快来看看我的优秀表现吧。";
        $shareParams = [
            'title'=> $title,
            'imgUrl'=>$imgUrl,
            'desc'=>$shareddesc,
            'link'=>$this->share_url
        ];
        return $shareParams;
    }

    public function qrcode() {
        $url = $this->share_url;
        $datepath = date("Ymd");
        $md5filename = $datepath.'/'.md5($url).'.png';
        $md5path = ROOT_PATH . '/public/uploads/'.$md5filename;
        if (!file_exists($md5path)) {
            $workpath = ROOT_PATH . '/public/uploads/' . $datepath;
            if (!file_exists($workpath)) {
                mkdir($workpath);
            }
            $qrCode = new QrCode();
            $qrCode
                ->setText($url)
                ->setSize(250)
                ->setPadding(15)
                ->setLogoSize(50)
                ->setImageType(QrCode::IMAGE_TYPE_PNG);
            $qrCode->setLogo(ROOT_PATH . 'public/assets/img/adminlogo.png');
            $qrCode->save($md5path);
        }
        return ["/uploads/".$md5filename,$url];
    }

    public function amount() {

    }
}
