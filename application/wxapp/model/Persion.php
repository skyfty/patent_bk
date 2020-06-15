<?php

namespace app\wxapp\model;

use think\Model;

class Persion extends \app\common\model\Persion
{
    // 追加属性
    protected $append = [
        'birthday_text',
        'sex_text',
        'educational_text',
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;
        });
        parent::init();
    }
    public function getBirthdayTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['birthday']) ? $data['birthday'] : '');
        return date("Y-m-d",$value);
    }

    private function getSelectFieldText($value, $field_name) {
        $list= Fields::get(['model_table'=>'persion','name'=>$field_name],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getSexTextAttr($value, $data)
    {
        $value = $value ? $value : $data['sex'];
        return $this->getSelectFieldText($value, 'sex');
    }
    public function getEducationalTextAttr($value, $data)
    {
        $value = $value ? $value : $data['educational'];
        return $this->getSelectFieldText($value, 'educational');

    }
}

