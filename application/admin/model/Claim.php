<?php

namespace app\admin\model;

use think\Model;
use app\admin\library\Auth;

class Claim extends  \app\common\model\Claim
{

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['owners_model_id'] = $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

    }
}
