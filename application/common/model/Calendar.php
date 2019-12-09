<?php

namespace app\common\model;

use think\Model;

class Calendar extends Model
{
    // 表名
    protected $name = 'calendar';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'starttime_text',
        'endtime_text',
        'status_text',
    ];

    public function getStatuslist()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden'), 'expired' => __('Expired'), 'completed' => __('Completed')];
    }

    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['starttime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getEndtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['endtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setStarttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setEndtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function course()
    {
        return $this->hasOne('course','id','course_model_id')->setEagerlyType(0)->joinType("LEFT");
    }

    public function staff()
    {
        return $this->hasOne('staff','id','staff_model_id')->setEagerlyType(0)->joinType("LEFT");
    }

}
