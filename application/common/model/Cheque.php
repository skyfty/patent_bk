<?php

namespace app\common\model;

use think\Model;

class Cheque extends Model
{
    // 表名
    protected $name = 'cheque';
    public $keywordsFields = ['name'];

    public static function getTypeList()
    {
        return Fields::get(['model_table'=>'account','name'=>'type'],[],true)->content_list;
    }

    public function getTypeTextAttr($value, $data)
    {
        $typelist = self::getTypeList();
        $value = $value ? $value : $data['type'];
        return $value && isset($typelist[$value]) ? $typelist[$value] : $value;
    }

    public static function getMoldList()
    {
        return Fields::get(['model_table'=>'account','name'=>'mold'],[],true)->content_list;
    }

    public function getMoldTextAttr($value, $data)
    {
        $typelist = self::getMoldList();
        $value = $value ? $value : $data['mold'];
        return $value && isset($typelist[$value]) ? $typelist[$value] : $value;
    }

    public function getInflowTextAttr($value, $data)
    {
        $dd = Modelx::get($data['inflow_id'],[],true);
        if ($dd) {
            return Modelx::get($data['inflow_id'],[],true)->name;
        } else {
            return $value;
        }
    }

    public function getInflowModelIdTextAttr($value, $data)
    {
        $dd = Cheque::get($data['inflow_cheque_id'],[],true);
        if ($dd) {
            return $dd->getData("name");
        } else {
            return $value;
        }
    }

    public function getRelatedTextAttr($value, $data)
    {
        if ($data['related_id']) {
            return Modelx::get($data['related_id'],[],true)->name;
        }
        return $value;
    }

    public function getReckonTextAttr($value, $data)
    {
        if ($data['reckon_id']) {
            return Modelx::get($data['reckon_id'],[],true)->name;
        }
        return $value;
    }

    public function inflow() {
        return $this->belongsTo('Cheque', 'inflow_cheque_id', '', [], 'LEFT')->setEagerlyType(0);
    }
}
