<?php

namespace app\common\model;

class Promotion extends Cosmetic
{
    // 表名
    protected $name = 'promotion';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PN%06d", $maxid);
        });

        self::afterDelete(function($row){
            Provider::destroy(['promotion_model_id'=>$row['id']]);
            model($row['relevance_model_type'])->where("id", $row['relevance_model_id'])->delete();
        });
    }
    public function getPictureListAttr($value, $data)
    {
        $value = $value ? $value : $data['pictures'];
        if ($value) {
            $value = explode(",", $value);
        }
        return $value ? $value : [];
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function genre() {
        return $this->hasOne('genre','id','genre_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function species()
    {
        return $this->hasOne('species','id','species_cascader_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function relevance()
    {
        return $this->morphTo("relevance_model");
    }

}
