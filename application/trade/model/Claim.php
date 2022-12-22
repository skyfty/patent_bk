<?php

namespace app\trade\model;

use think\Model;
use app\admin\library\Auth;

class Claim extends  \app\common\model\Claim
{
    // 追加属性
    protected $append = [
        "principal"
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
