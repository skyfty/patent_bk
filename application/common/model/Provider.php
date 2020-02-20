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
        return $this->hasOne('promotion','id','promotion_model_id')->joinType("LEFT")->field('id,idcode,name')->setEagerlyType(0);
    }

    public function relevance()
    {
        return $this->morphTo("relevance_model_id","relevance_model_type");
    }

    public function amount() {

    }
}
