<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

class Warerange extends \app\common\model\Warerange
{
    protected static function init()
    {

        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }
    public function catenate()
    {
        return $this->morphMany('Catenate', 'ware');
    }
}
