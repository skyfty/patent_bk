<?php

namespace app\common\model;

use fast\Random;
use think\Model;

class Company extends Cosmetic
{
    // 表名
    protected $name = 'company';
    public $keywordsFields = [];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CO%06d", $maxid);
        });
    }

    public function industry() {
        return $this->hasManyComma('industry','id','industry_model_id');
    }
    public function principal()
    {
        return $this->morphOne('Principal', 'substance');
    }
}

