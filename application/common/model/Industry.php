<?php

namespace app\common\model;

use think\Model;

class Industry extends Cosmetic
{
    // 表名
    protected $name = 'industry';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("IN%06d", $maxid);
        });
    }


    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
