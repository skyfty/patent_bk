<?php

namespace app\common\model;

use think\Model;

class Courseware extends Cosmetic
{
    // 表名
    protected $name = 'courseware';

    // 追加属性
    protected $append = [
    ];
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();
        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CO%06d", $maxid);
        });
    }


    public function lecture() {
        return $this->hasOne('lecture','id','lecture_model_id')->setEagerlyType(0);
    }
}
