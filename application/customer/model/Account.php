<?php

namespace app\customer\model;

class Account extends \app\common\model\Account
{
    protected static function init()
    {

        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;
        });
        parent::init();
    }

    public static function getList($list, $tag)
    {
        return $list;
    }
}
