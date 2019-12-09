<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;

use fast\Tree;

trait PresetModel
{
    public function getPrimaryAttr($value,$data)
    {
        $value = $value?$value:$data['primary'];
        return json_decode($value, true);
    }

    public function getSecondAttr($value,$data)
    {
        $value = $value?$value:$data['second'];
        return json_decode($value, true);
    }

    public function getThirdAttr($value,$data)
    {
        $value = $value?$value:$data['third'];
        return json_decode($value, true);
    }
    public function getEntireAttr($value,$data)
    {
        $value = $value?$value:$data['entire'];
        return json_decode($value, true);
    }
    public function getDetailAttr($value,$data)
    {
        $value = $value?$value:$data['detail'];
        return json_decode($value, true);
    }

    public function assembly() {
        return $this->hasOne('assembly','id','assembly_model_id')->setEagerlyType(0);
    }
    public function behavior() {
        return $this->hasOne('behavior','id','behavior_model_id')->setEagerlyType(0);
    }
    public function warehouse() {
        return $this->hasOne('warehouse','id','warehouse_model_id')->setEagerlyType(0);
    }
    public function getDetailTextAttr($value,$data) {
        $ee = $this->getDetailAttr($value,$data);
        $ret = [];
        foreach($ee as $v) {
            $ret[] = $v['data'];
        }
        return $ret?implode("", $ret):"";
    }
}