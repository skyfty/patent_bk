<?php

namespace app\admin\model;

use think\Model;

class Department extends Model
{
    // 表名
    protected $name = 'auth_department';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    public function getNameAttr($value, $data)
    {
        return __($value);
    }

    // 追加属性
    protected $append = [
        'status_text'
    ];
    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden'), 'locked' => __('Locked')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }





}
