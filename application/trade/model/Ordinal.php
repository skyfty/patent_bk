<?php

namespace app\trade\model;

use app\admin\library\Auth;
use think\Model;
use traits\model\SoftDelete;

class Ordinal extends    \app\common\model\Ordinal
{
// 追加属性
    public $append = ["condition_text"];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['branch_model_id'] = 0;
            $row['owners_model_id'] = $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

    }
}
