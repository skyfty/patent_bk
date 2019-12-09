<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

class Account extends \app\common\model\Account
{
    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }
}
