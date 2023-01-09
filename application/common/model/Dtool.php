<?php

namespace app\common\model;

use think\Model;

class Dtool extends  Cosmetic
{
    // 表名
    protected $name = 'dtool';

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
