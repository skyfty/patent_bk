<?php

namespace app\common\model;

use think\Model;

class Preset extends Cosmetic
{
    // 表名
    protected $name = 'preset';

    // 追加属性
    protected $append = [
    ];
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PR%06d", $maxid);
            $row['pid'] = $row['prelecture_model_id'];
        });
    }
}
