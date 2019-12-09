<?php

namespace app\admin\model;

use think\Model;
use app\admin\library\Auth;

class Trade extends \app\common\model\Trade
{
    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }

}

