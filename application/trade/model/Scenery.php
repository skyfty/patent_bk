<?php

namespace app\trade\model;

use think\Model;

class Scenery extends Model
{
    // 表名
    protected $name = 'scenery';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected static function init()
    {

    }

    public function sight()
    {
        return $this->hasMany('Sight');
    }
}
