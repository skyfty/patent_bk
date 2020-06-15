<?php

namespace app\wxapp\model;

use think\Model;

class Procedure extends   \app\common\model\Procedure
{
    protected static function init()
    {
        self::beforeInsert(function($row){
            $row['creator_model_id'] =2;
        });
        parent::init();
    }
}
