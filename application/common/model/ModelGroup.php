<?php

namespace app\common\model;

use think\Db;
use think\Model;
use traits\model\SoftDelete;

class ModelGroup extends Model
{
    protected $name = 'group';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    public $keywordsFields = ["title"];



    protected static function init()
    {
        parent::init();
    }

    public function getModelTextAttr($value, $data) {
        return $this->model->name;
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'),'hidden' => __('Hidden'),'locked' => __('Locked')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 读取分类类型
     * @return array
     */
    public static function getTypeList()
    {
        return ['fixed' => __('Fixed'),'cond' => __('Cond')];

    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getContentAttr($value, $data)
    {
        if ($value != "") {
            if ($value[0] == "[") {
                $value = json_decode($value);
            } else {
                $value = explode(",", $value);
            }
        }
        return $value;
    }

    public function setContentAttr($value, $data)
    {
        if (is_array($value) && count($value) > 0) {
            if (isset($value[0]['condition'])) {
                $value = json_encode($value);
            } else {
                $value = implode(",", $value);
            }
        }
        return $value;
    }

    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->bind([
            'branch_name'	=> 'name',
        ]);
    }
}
