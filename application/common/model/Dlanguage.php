<?php

namespace app\common\model;

use think\Model;

class Dlanguage extends  Cosmetic
{
    // 表名
    protected $name = 'dlanguage';

    public $keywordsFields = [];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CL%06d", $maxid);
        });


        self::afterDelete(function ($row){
            model("codesegment")->where("dlanguage_model_id", $row['id'])->delete();
        });


    }

}
