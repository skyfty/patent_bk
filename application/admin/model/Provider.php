<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Config;
use think\Db;
use think\Exception;
use think\Hook;

class Provider extends \app\common\model\Provider
{
    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['owners_model_id'] =   $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }

    public function expire() {
    }


    public function dispatch() {
        $cutime = time();
        $spanstarttime = round(($this['starttime'] - $cutime) / 60);
        $spanendtime = round(($this['endtime'] - $cutime) / 60);


    }
}
