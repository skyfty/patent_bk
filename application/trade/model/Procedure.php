<?php

namespace app\trade\model;

use app\admin\library\Auth;

class Procedure extends   \app\common\model\Procedure
{
// 追加属性

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }
}
