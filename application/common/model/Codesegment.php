<?php

namespace app\common\model;

use think\Model;

class Codesegment extends  Cosmetic
{
    // 表名
    protected $name = 'codesegment';

    public $keywordsFields = [];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CL%06d", $maxid);
        });
    }

    public function dlanguage() {
        return $this->hasOne('dlanguage','id','dlanguage_model_id')->joinType("LEFT")->field('*')->setEagerlyType(0);
    }

}
