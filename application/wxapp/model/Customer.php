<?php

namespace app\wxapp\model;

use think\Model;

class Customer extends \app\common\model\Customer
{
    protected static function init()
    {
        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;
        });
        parent::init();
    }

    public static function getList($list, $tag)
    {
        return $list;
    }

    public function getBirthdayTextAttr($value, $data) {
        $value = $value ? $value : $data['birthday'];
        return $value?date("m-d",$value):"";
    }

    public function getAgeTextAttr($value, $data) {
        $value = $value ? $value : $data['birthday'];
        return $value?date2age($value):"";

    }
    public function getNicknameAttr($value, $data) {
        $value = $value ? $value : $data['nickname'];
        return $value?$value:$data['name'];

    }
    public function getSelectField($name, $value) {
        $list= Fields::get(['name'=>$name,'model_table'=>$this->name],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getSexTextAttr($value, $data) {
        $value = $value ? $value : $data['sex'];
        return $this->getSelectField('sex', $value);
    }

    public function getRankTextAttr($value, $data) {
        $value = $value ? $value : $data['rank'];
        return $this->getSelectField('rank', $value);
    }

}

