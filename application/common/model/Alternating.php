<?php

namespace app\common\model;

use think\Model;

class Alternating extends Cosmetic
{
    // 表名
    protected $name = 'alternating';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("IN%06d", $maxid);
        });

        $updatefieldname = function($row){
            if ($row['type'] == "custom") {
                $row['field_name'] =  \fast\Pinyin::get($row['name']);
            } else {
                $field = model("fields")->get($row['field_model_id']);
                $row['field_name'] = $field->name;
            }
            $row['relevance_model_type'] =  $row->procedure->relevance_model_type;
        };
        self::beforeInsert($updatefieldname); self::beforeUpdate($updatefieldname);
    }

    public function field() {
        return $this->hasOne('fields','id','field_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function procedure() {
        return $this->hasOne('procedure','id','procedure_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
