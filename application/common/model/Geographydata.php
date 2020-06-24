<?php

namespace app\common\model;

class Geographydata extends Cosmetic
{
    // 表名
    protected $name = 'geographydata';
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
