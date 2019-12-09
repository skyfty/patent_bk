<?php

namespace app\customer\model;

class Business extends  \app\common\model\Business
{
    // 追加属性
    protected $append = [
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;
        });
        parent::init();

    }

}