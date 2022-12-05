<?php

namespace app\trade\model;

use think\Model;
use think\Session;

class Admin extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    public $keywordsFields = ["username","nickname"];
    protected $append = [
    ];

    protected static function init()
    {
        parent::init();
    }

    // 密码加密
    protected function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }

    public function staff() {
        return $this->hasOne('staff', 'id', 'staff_id', [], 'LEFT')->setEagerlyType(0);
    }
}
