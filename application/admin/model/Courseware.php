<?php

namespace app\admin\model;

use think\Model;
use app\admin\library\Auth;

class Courseware extends \app\common\model\Courseware
{

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

    }

    use \app\admin\library\traits\PresetModel;
}
