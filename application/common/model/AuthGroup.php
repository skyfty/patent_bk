<?php

namespace app\common\model;

use think\Model;

class AuthGroup extends Model
{
    protected $name = 'auth_group';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected static function init()
    {
        parent::init();
        self::afterUpdate(function($row){
            if ($row->branch_id == 0 && $row->inherit == 1) {
                $changeData = $row->readonly("updatetime")->getChangedData();
                if (isset($changeData['rules'])) {
                    $otherGroups = model('AuthGroup')->where('name', $row['name'])->where("id", 'NEQ', $row->id)->select();
                    foreach ($otherGroups as $group) {
                        $group->save(['rules' => $row['rules']]);
                    }
                }
            }
        });
    }

    public function getNameAttr($value, $data)
    {
        return __($value);
    }

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
