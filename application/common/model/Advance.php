<?php

namespace app\common\model;

use think\Model;

class Advance extends Cosmetic
{
    // 表名
    protected $name = 'advance';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("AD%06d", $maxid);
        });

        $updatefieldname = function($row){

        };
        self::beforeInsert($updatefieldname); self::beforeUpdate($updatefieldname);
    }

    public function field() {
        return $this->hasOne('fields','id','field_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function plan() {
        return $this->hasOne('plan','id','plan_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function professional() {
        return $this->hasOne('professional','id','professional_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
