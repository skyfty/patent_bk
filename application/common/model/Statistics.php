<?php

namespace app\common\model;

use think\Model;
use think\Session;

class Statistics extends Model
{
    protected $name = 'statistics';

    protected $autoWriteTimestamp = 'int';
    protected $updateTime = 'updatetime';
    protected $createTime = 'createtime';

    protected static function init()
    {
        parent::init();
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_id')->setEagerlyType(0);
    }
}
