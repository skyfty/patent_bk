<?php

namespace app\common\model;

use think\Model;

class Principal extends  Cosmetic
{
    // 表名
    protected $name = 'principal';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PR%06d", $maxid);
        });
    }

    public function principalclass() {
        return $this->hasOne('principalclass','id','principalclass_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
