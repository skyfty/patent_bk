<?php

namespace app\admin\model;

use think\Model;
use app\admin\library\Auth;

class Business extends   \app\common\model\Business
{
    // 追加属性

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['owners_model_id'] = $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }


}
