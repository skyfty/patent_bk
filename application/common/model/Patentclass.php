<?php

namespace app\common\model;

use think\Model;

class Patentclass extends  Cosmetic
{
    // 表名
    protected $name = 'patentclass';
    public $keywordsFields = ["name", "idcode"];

    // 追加属性
    protected $append = [
        'full_name',
        "full_id"
    ];
    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PA%06d", $maxid);
        });
    }

    public function getFullNameAttr($value, $data)
    {
        $name = "";
        if ($data['pid'] != 0) {
            $name= self::get($data['pid'])->full_name;
            $name.="/";
        }
        $name .= $data['name'];
        return $name;
    }

    public function getFullIdAttr($value, $data)
    {
        $id = "";
        if ($data['pid'] != 0) {
            $id= self::get($data['pid'])->full_id;
            $id.=",";
        }
        $id .= $data['id'];
        return $id;
    }
}
