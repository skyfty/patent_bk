<?php

namespace app\customer\model;

use think\Model;

class Company extends \app\common\model\Company
{
    // 追加属性
    protected $append = [
        'registerdate_text',
        'type_text',
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;

        });
        parent::init();
    }
    public function getRegisterdateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['registerdate']) ? $data['registerdate'] : '');
        return $value?date("Y-m-d",$value):$value;
    }

    private function getSelectFieldText($value, $field_name) {
        $list= Fields::get(['model_table'=>'company','name'=>$field_name],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        return $this->getSelectFieldText($value, 'type');
    }

    public function getRegisteraddressAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['registeraddress']) ? $data['registeraddress'] : '');
        return str_replace("/", " ", $value);
    }

    public function setRegisteraddressAttr($value)
    {
        return str_replace(" ", "/", $value);
    }

}

