<?php

namespace app\common\model;

use think\Model;

class Commission extends Model
{
    // 表名
    protected $name = 'commission';
    
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
        'status_text',
        'rank_text',
        'full_name',
        "full_id"

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

    public function getRankList()
    {
        return ['ministerial' => __('部级'), 'bureau' => __('局级'), 'office' => __('厅级')];
    }

    public function getRankTextAttr($value, $data)
    {
        $value = $value ? $value : $data['rank'];
        $list = $this->getRankList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getFullNameAttr($value, $data)
    {
        $name = "";
        if ($data['pid'] != 0) {
            $name= self::get($data['pid'])->full_name;
            $name.="/";
        }
        $name .= $data['name'];
        return $name;
    }

    public function getFullIdAttr($value, $data)
    {
        $id = "";
        if ($data['pid'] != 0) {
            $id= self::get($data['pid'])->full_id;
            $id.=",";
        }
        $id .= $data['id'];
        return $id;
    }
}
