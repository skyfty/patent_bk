<?php

namespace app\common\model;

use think\Model;

class Stem extends Cosmetic
{
    // 表名
    protected $name = 'stem';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("ST%06d", $maxid);
        });
    }
}
