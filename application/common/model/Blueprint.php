<?php

namespace app\common\model;

class Blueprint extends Cosmetic
{
    // 表名
    protected $name = 'blueprint';
    public $keywordsFields = ["name", "idcode"];


    public function getConditionListAttr($value, $data)
    {
        $condition = [];
        foreach(explode("\r\n", $data['condition']) as $k=>$v) {
            if ($v == "") continue;
            $v = explode("|", $v);
            if (count($v) == 2) {
                $condition[$v[0]] = $v[1];
            } else {
                $condition[$v[0]] = $v[0];
            }
        }
        return $condition;
    }

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("BL%06d", $maxid);
        });
    }
}
