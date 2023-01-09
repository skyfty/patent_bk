<?php

namespace app\common\model;

use think\Model;

class Osystem extends  Cosmetic
{
    // 表名
    protected $name = 'osystem';

    public $keywordsFields = [];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CL%06d", $maxid);
        });
    }

}
