<?php

namespace app\customer\model;

use think\Model;
use app\admin\library\Auth;

class Aptitude extends  \app\common\model\Aptitude
{
    // 追加属性

    protected static function init()
    {
        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;
        });
        parent::init();
    }


}
