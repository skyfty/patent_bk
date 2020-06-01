<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Assembly extends Cosmetic
{
    // 表名
    protected $name = 'assembly';
    public $keywordsFields = ["name"];

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
            $row['idcode'] = sprintf("AS%06d", $maxid);
        });

        self::afterDelete(function($row){
            model("behavior")->destroy(["assembly_model_id"=>$row->id]);
        });
    }

    public function warehouse() {
        return $this->hasOne('warehouse','id','warehouse_model_id')->setEagerlyType(0);
    }
}
