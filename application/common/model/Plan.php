<?php

namespace app\common\model;

use fast\Random;
use think\Model;

class Plan extends Cosmetic
{
    // 表名
    protected $name = 'plan';
    public $keywordsFields = [];


    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PN%06d", $maxid);
        });
    }

    public function getStatusAttr($value, $data) {

        return $data['status'];
    }

    public function species() {
        return $this->hasOne('species','id','species_model_id')->joinType("LEFT")->field('*')->setEagerlyType(0);
    }

}

