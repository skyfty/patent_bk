<?php

namespace app\common\model;

use think\Model;

class Genre extends Model
{
    // 表名
    protected $name = 'genre';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'full_name'
    ];
    public $keywordsFields = ["name"];

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
        return $this->hasOne('genre','id','pid')->joinType("LEFT")->setEagerlyType(0);
    }


}
