<?php

namespace app\common\model;

use think\Model;

class Invent extends Professional
{
    // 表名
    protected $name = 'invent';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("IN%06d", $maxid);
        });
    }

    public function getInitPromotionData($species) {
        $data = [];
        $data["principal_model_id"]=$this['principal_model_id'];
        return $data;
    }

    public function principal()
    {
        return $this->hasOne('principal','id','principal_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function patentclass()
    {
        return $this->hasOne('patentclass','id','patentclass_cascader_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
