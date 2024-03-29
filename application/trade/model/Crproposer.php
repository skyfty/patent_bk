<?php

namespace app\trade\model;

use app\admin\library\Auth;
use think\Model;
use traits\model\SoftDelete;

class Crproposer extends  \app\common\model\Crproposer
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
