<?php

namespace app\wxapp\model;

use think\Model;

class Principal extends  \app\common\model\Principal
{
    // 追加属性

    protected static function init()
    {
        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;
            $row['branch_model_id'] = 0;
        });
        parent::init();
    }

}
