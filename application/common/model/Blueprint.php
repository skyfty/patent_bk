<?php

namespace app\common\model;

class Blueprint extends Cosmetic
{
    // 表名
    protected $name = 'blueprint';
    public $keywordsFields = ["name", "idcode"];


    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("BL%06d", $maxid);
        });
    }
}
