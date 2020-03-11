<?php

namespace app\common\model;

use think\Model;

class Professional extends Cosmetic
{
    protected static function init()
    {
        parent::init();

        self::afterInsert(function($row){
            $model_name = $row->name;
            $species = model("species")->where("model",$model_name)->cache(true)->find();
            model("promotion")->create([
                'branch_model_id'=>$row['branch_model_id'],
                'relevance_model_id'=>$row['id'],
                'relevance_model_type'=>$model_name,
                'species_cascader_id'=>$species['id'],
            ]);

        });
    }

    public function species()
    {
        return $this->hasOne('species','id','species_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function relevance() {
        return $this->morphOne('promotion', 'relevance_model');
    }

    public function promotion() {
        return $this->hasOne('promotion','id','promotion_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
