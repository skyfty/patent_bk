<?php

namespace app\trade\model;

use app\admin\library\Auth;

class Promotion extends  \app\common\model\Promotion
{
    // 追加属性
    protected $append = [
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }
}
