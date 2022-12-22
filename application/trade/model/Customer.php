<?php

namespace app\trade\model;

use app\admin\library\Auth;
use fast\Random;
use think\Model;

class Customer extends \app\common\model\Customer
{
    // 追加属性
    protected $append = [
        "claims"
    ];


    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }
    public function claims()
    {
        return $this->hasMany('claim','customer_model_id');
    }
}

