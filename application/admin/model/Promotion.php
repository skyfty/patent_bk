<?php

namespace app\admin\model;

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
            $row['owners_model_id'] =   $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }
}
