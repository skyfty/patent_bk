<?php

namespace app\admin\model;

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
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        $updatePolicy = function($row){
            $row->policy->updateCondition();
        };
        self::afterInsert($updatePolicy);self::afterUpdate($updatePolicy);self::afterDelete($updatePolicy);
    }
}
