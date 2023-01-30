<?php

namespace app\common\model;

use think\Model;

class Crlanguage extends  Cosmetic
{
    // 表名
    protected $name = 'crlanguage';

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
    public function dlanguage() {
        return $this->hasOne('dlanguage','id','dlanguage_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
