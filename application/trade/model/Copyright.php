<?php

namespace app\trade\model;

use think\Model;
use app\admin\library\Auth;

class Copyright extends   \app\common\model\Copyright
{
    // 追加属性
    protected $append = [
        "develop_os",
        "develop_tool",
        "auxiliary_software",
        "running_software",
        "dlanguage",
        "company"
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
