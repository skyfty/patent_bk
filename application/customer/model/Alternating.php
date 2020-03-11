<?php

namespace app\customer\model;

use think\Model;

class Alternating extends  \app\common\model\Alternating
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
