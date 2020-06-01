<?php

namespace app\common\model;

class Expound extends Cosmetic
{
    // 表名
    protected $name = 'expound';

    // 追加属性
    protected $append = [
    ];
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();
        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("EX%06d", $maxid);
            $row['pid'] = $row['exlecture_model_id'];
        });
    }
    use \app\admin\library\traits\PresetModel;

    public function animation() {
        return $this->hasOne('animation','id','animation_id')->setEagerlyType(0);
    }

}
