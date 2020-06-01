<?php

namespace app\common\model;

use think\Model;

class Warehouse extends Cosmetic
{
    // 表名
    protected $name = 'warehouse';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'pid','id'
    ];
    public $keywordsFields = ["name", "idcode"];
    
    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("WA%06d", $maxid);
        });

        self::afterDelete(function($row){
            model("assembly")->where("warehouse_model_id",$row['id'])->delete();
        });

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function equipment()
    {
        return $this->hasOne('equipment','id','model_id')->setEagerlyType(0);
    }
    public function material()
    {
        return $this->hasOne('material','id','model_id')->setEagerlyType(0);
    }
    public function middleware()
    {
        return $this->hasOne('middleware','id','model_id')->setEagerlyType(0);
    }
    public function repertory()
    {
        return $this->hasOne('repertory','id','model_id')->setEagerlyType(0);
    }
    public function grade()
    {
        return $this->hasOne('grade','id','model_id')->setEagerlyType(0);
    }
    public function datum()
    {
        return $this->hasOne('datum','id','model_id')->setEagerlyType(0);
    }
}
