<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;
use traits\model\SoftDelete;

class Codesegment extends  \app\common\model\Codesegment
{

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        $beforeupdate = function($row){
            $row['lines'] = substr_count($row['code'], "\n");
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);
    }

}
