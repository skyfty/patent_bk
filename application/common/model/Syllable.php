<?php

namespace app\common\model;

use think\Model;

class Syllable extends Cosmetic
{
    // 表名
    protected $name = 'syllable';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CO%06d", $maxid);
        });
    }

    public function getConditionListAttr($value, $data)
    {
        $condition = [];
        foreach(explode("\r\n", $data['condition']) as $k=>$v) {
            $v = explode("|", $v);
            $condition[$v[0]] = $v[1];
        }
        return $condition;
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
