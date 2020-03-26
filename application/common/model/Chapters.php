<?php

namespace app\common\model;


class Chapters extends Cosmetic
{
    // 表名
    protected $name = 'chapters';
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
