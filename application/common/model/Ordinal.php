<?php

namespace app\common\model;

use think\Model;

class Ordinal extends Cosmetic
{
    // 表名
    protected $name = 'ordinal';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CO%06d", $maxid);
        });
    }

    static public function getConditionList() {
        return [
            ">"=>"大于",
            "<"=>"小于",
            ">="=>"大于等于",
            "<="=>"小于等于",
            "LIKE %...%"=>"包含",
            "NOT LIKE %...%"=>"不包含",
            "="=>"是",
            "!="=>"不是",
            "IS NULL"=>"为空",
            "IS NOT NULL"=>"不为空",
        ];
    }

    public function getConditionTextAttr($value, $data)
    {
        $value = $value ? $value : $data['condition'];
        if ($data['type'] == "custom") {
            $list = self::getConditionList();
        } else {
            $list = $this->syllable->condition_list;
        }
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function syllable() {
        return $this->hasOne('syllable','id','syllable_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function policy() {
        return $this->hasOne('policy','id','policy_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
