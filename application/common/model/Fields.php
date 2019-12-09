<?php

namespace app\common\model;

use think\Model;

class Fields extends Model
{
    // 表名
    protected $name = 'fields';

    public static $listField = ['select', 'selects', 'checkbox', 'radio', 'array','sortable'];


    public function getContentListAttr($value, $data)
    {
        if (in_array($data['type'], self::$listField))
            return Config::decode($data['content']);
        if ($data['type'] == "model" && $data['content']) {
            $content = json_decode($data['content'], true);
            if ($content)
                return $content;
        }
        return $data['content'];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden'), 'locked' => __('Locked'), 'disabled'=>__('Disabled')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getFieldsAttr($value, $data)
    {
        return is_array($value) ? $value : ($value ? explode(',', $value) : []);
    }

    public function fields()
    {
        return $this->hasMany('Fields','model_id');
    }

}
