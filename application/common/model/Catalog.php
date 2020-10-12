<?php

namespace app\common\model;


class Catalog extends Cosmetic
{
    // 表名
    protected $name = 'catalog';
    public $keywordsFields = ["name", "idcode"];

    // 追加属性
    protected $append = [
        'full_name'
    ];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("BL%06d", $maxid);
        });
    }


    public function getRootNameAttr($value, $data) {
        if ($data['pid'] != 0) {
            return self::get($data['pid'])->getData("name");
        }
        return $data['name'];
    }

    public function getFullNameAttr($value, $data)
    {
        $name = $data['name'];
        if ($data['pid'] != 0) {
            $name.= "/".self::get($data['pid'])->full_name;
        }
        return $name;
    }

    public function parent()
    {
        return $this->hasOne('catalog','id','pid')->joinType("LEFT")->setEagerlyType(0);
    }
}
