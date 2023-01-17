<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;
use app\admin\library\Auth;

class Alternating extends  \app\common\model\Alternating
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
