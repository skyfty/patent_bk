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

    protected static function upstat($row) {
        $quantity = self::where(['branch_model_id'=>$row->branch_model_id])->count();
        $where = ["table"=>"provider","field"=>'quantity', 'branch_model_id'=>$row->branch_model_id];
        $stat = new Statistics();
        if (($ns = $stat->where($where)->find()) == null) {
            $stat->data($where, true);
        } else $stat = $ns;
        $stat->save(['value' => $quantity]);
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


        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("PV%06d", $maxid);
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
