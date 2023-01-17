<?php

namespace app\trade\model;

use think\Model;
use app\admin\library\Auth;

class Aptitude extends  \app\common\model\Aptitude
{
    // 追加属性
    protected $append = [
        "company",
        "promotion"

    ];



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
