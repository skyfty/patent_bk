<?php

namespace app\common\model;

use think\App;
use think\Loader;
use think\Model;

class Principal extends  Cosmetic
{
    // 表名
    protected $name = 'principal';
    public $keywordsFields = ["name", "idcode"];
    public $append = [];

    protected function initialize()
    {
        parent::initialize();
        if (isset($this['principalclass']) &&  $this['principalclass']) {
            $this->assignTimestampFieldConvert($this['principalclass']['model_type']);
        }
    }

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

        self::afterInsert(function($row){
            $row['substance_type'] = $row['principalclass']['model_type'];
            $substance = model($row['substance_type'])->create(['principal_model_id'=>$row['id']]);
            $row['substance_id'] = $substance['id'];
            $row->save();
        });
        self::afterDelete(function($row){
            model($row['principalclass']['model_type'])->where(['principal_model_id'=>$row['id']])->delete();
            model("claim")->where(['principal_model_id'=>$row['id']])->delete();
        });

    }

    public function principalclass() {
        return $this->hasOne('principalclass','id','principalclass_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function substance()
    {
        return $this->morphTo();
    }

    public function promotions()
    {
        return $this->hasMany('promotion','principal_model_id');
    }
}
