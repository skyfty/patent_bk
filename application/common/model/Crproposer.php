<?php

namespace app\common\model;

use think\Model;

class Crproposer extends  Cosmetic
{
    // 表名
    protected $name = 'crproposer';

    public $keywordsFields = [];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CL%06d", $maxid);
        });

    }

    public function copyright() {
        return $this->hasOne('copyright','id','copyright_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
