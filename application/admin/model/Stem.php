<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

class Stem extends   \app\common\model\Stem
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
