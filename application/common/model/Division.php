<?php

namespace app\common\model;


class Division extends Cosmetic
{
    // 表名
    protected $name = 'division';
    public $keywordsFields = ["name", "idcode"];


    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("BL%06d", $maxid);
        });

       self::afterDelete( function($row){
          model("chapters")->where("division_model_id", $row['id'])->delete();
       });
    }
    public function chapters() {
        return $this->hasOne('chapters','id','chapters_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function paragraphs() {
        return $this->hasMany('paragraph','division_model_id');
    }
}
