<?php

namespace app\common\model;

use fast\Random;
use think\Model;

class Persion extends Cosmetic
{
    // 表名
    protected $name = 'persion';
    public $keywordsFields = [];


    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PE%06d", $maxid);
        });
    }

    public function invent() {
        return $this->morphOne('invent', 'relevance_model');
    }

    public function principal()
    {
        return $this->morphOne('Principal', 'substance');
    }
}

