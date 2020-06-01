<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;
use app\common\model\Config;

class Assembly extends \app\common\model\Assembly
{
    // 追加属性
    use \app\admin\library\traits\Adjective;

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

    }
    protected function setAdjectiveAttr($value,$data)
    {
        if (!is_string($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }
        return $value;
    }
}
