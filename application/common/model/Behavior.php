<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Behavior extends Cosmetic
{
    // 表名
    protected $name = 'behavior';
    public $keywordsFields = ["name"];

    protected static function init()
    {
        parent::init();

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                try {
                    $row['slug'] = \fast\Pinyin::get($row['name']);
                } catch(\Exception $e) {
                }
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);
    }

    public function warehouse() {
        return $this->hasOne('warehouse','id','warehouse_model_id')->setEagerlyType(0);
    }
    public function assembly() {
        return $this->hasOne('assembly','id','assembly_model_id')->setEagerlyType(0);
    }
}
