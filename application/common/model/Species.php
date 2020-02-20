<?php

namespace app\common\model;

class Species extends Cosmetic
{
    // 表名
    protected $name = 'species';
    public $keywordsFields = ["name", "idcode"];


    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("SP%06d", $maxid);
        });
    }

}
