<?php

namespace app\trade\model;
use app\admin\library\Auth;

use think\Model;

class Principalclass extends  \app\common\model\Principalclass
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
