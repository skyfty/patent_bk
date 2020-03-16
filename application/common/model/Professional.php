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
            $procedure = model("procedure")->where("relevance_model_type",$model_name)->find();
            $data = $row->getInitPromotionData($species);;
            $data = array_merge([
                'name'=>$species['name'],
                'branch_model_id'=>$row['branch_model_id'],
                'relevance_model_id'=>$row['id'],
                'relevance_model_type'=>$model_name,
                'species_cascader_id'=>$species['id'],
                'procedure_model_id'=>$procedure['id'],
            ],$data);
            $promotion = model("promotion")->create($data);
            $row->save(['promotion_model_id'=>$promotion['id']]);
        });
    }

    public function produceDocument($procedure) {
        if (is_numeric($procedure)) {
            $procedure = model("procedure")->get($procedure);
        }
        model("procshutter")->where("procedure_model_id", $procedure['id'])->delete();

        $alternatings = $procedure->alternatings;
        $shutterings = model("shuttering")->where("procedure_model_id", $procedure['id'])->select();
        foreach($shutterings as $shuttering) {
            $filename = $shuttering->produce($this, $alternatings);
            if ($filename) {
                model("procshutter")->create([
                    "procedure_model_id"=> $procedure['id'],
                    "file"=> $filename,
                    "name"=> $shuttering['name'],
                ]);
            }
        }
    }

    public function getInitPromotionData($species) {
        return [];
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
