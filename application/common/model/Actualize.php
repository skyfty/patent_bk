<?php

namespace app\common\model;

use think\Model;

class Actualize extends  Cosmetic
{
    // 表名
    protected $name = 'actualize';
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
