<?php

namespace app\api\model;

use think\Db;
use think\Exception;
use think\Model;

class Provider extends \app\common\model\Provider
{
    protected $append = [
        'name',
        'teacher',
        'time',
        'date'
    ];

    protected static function init()
    {
        parent::init();
    }

    public function getModelKeywordAttr($value) {
        $value = json_decode($value);
        return json_decode($value[0]);
    }

    public function getNameAttr($value, $data)
    {
        $value = $data['appoint_promotion_model_keyword'];
        if (!$value) return "";
        return $this->getModelKeywordAttr($value);
    }

    public function getTeacherAttr($value, $data)
    {
        $value = $data['staff_model_keyword'];
        if (!$value) return "";
        return $this->getModelKeywordAttr($value);
    }
    public function getTimeAttr($value, $data)
    {
        return $data['appoint_course'];
    }
    public function getDateAttr($value, $data)
    {
        return date("Y-m-d",$data['appoint_time']);
    }
}
