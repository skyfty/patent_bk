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
            $procedure = model("procedure")->where("relevance_model_type",$model_name)->order("order","asc")->find();
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
        $model_name = $this->name;
        if (is_numeric($procedure)) {
            $procedure = model("procedure")->get($procedure);
        }
        model("procshutter")->where("procedure_model_id", $procedure['id'])->where("status", "normal")->delete();

        $fields = model("fields")->where("model_table", $procedure['relevance_model_type'])->where("alternating", 1)->cache(true)->select();

        $alternatings_global = model("alternating")->where("relevance_model_type", $model_name)->where("scope", "global")->where("type", "custom")->select();
        $alternatings = $procedure->alternatings()->where("type", "custom")->where("scope", "procedure")->select();
        $alternatings = array_merge($alternatings_global, $alternatings);
        foreach($alternatings as $alternating) {
            $field = model("fields")->where("name", "name")->where("model_table", "procedure")->find();
            $field["type"] = $alternating['field_model_id'];
            $field["title"] = $alternating['name'];
            $field["name"] = $alternating['field_name'];
            $fields[] = $field;
        }

        if (isset($this['extend']) && $this['extend']) {
            foreach(json_decode($this['extend'], true) as $field_name=>$v) {
                $this[$field_name] = $v['value'];
            }
        }

        $shutterings = model("shuttering")->where("procedure_model_id", $procedure['id'])->select();
        foreach($shutterings as $shuttering) {
            if ($shuttering['file']) {
                $filename = $shuttering->produce($this, $fields);
                if ($filename) {
                    model("procshutter")->create([
                        "relevance_model_type"=> $procedure['relevance_model_type'],
                        "relevance_model_id"=> $this['id'],
                        "procedure_model_id"=> $procedure['id'],
                        "file"=> $filename,
                        "name"=> $shuttering['name'],
                        "status"=> "normal",
                        "shuttering_model_id"=> $shuttering['id'],
                    ]);
                }
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
