<?php

namespace app\admin\validate;

use think\Validate;
use app\common\validate\Cosmetic;

class Provider extends Cosmetic
{
    protected $name = "provider";

    protected $rule = [
        'package_model_id'       => 'require|checkPresell',
        'appoint_promotion_model_id'       => 'require|occupancy',
        'appoint_course'       => 'require|occupancy',
        'classroom_model_id'       => 'require|occupancy',
        'appoint_time'       => 'require|occupancy',
        'staff_model_id'       => 'require',
        'customer_model_id'       => 'require|checkCustomer',
    ];

    protected $scene = [
        'add'  => [
            'package_model_id',
            'staff_model_id',
            'appoint_promotion_model_id',
            'appoint_course',
            'classroom_model_id',
            'appoint_time',
            'customer_model_id'=>'require|checkCustomer|token',
        ],
        'exclusive'  => [
            'package_model_id'=> 'require',
            'staff_model_id',
            'appoint_promotion_model_id',
            'appoint_course',
            'classroom_model_id',
            'appoint_time',
            'customer_model_id'=>'require|checkCustomer|token',
        ],
        'batch'  => [
            'staff_model_id',
            'appoint_promotion_model_id',
            'appoint_course',
            'appoint_time',
        ],
        'reproduce'=>[
            'package_model_id',
            'staff_model_id',
            'appoint_promotion_model_id',
            'appoint_course',
            'classroom_model_id',
            'appoint_time',
            'customer_model_id'=>'require|checkCustomer',
        ]
    ];

    public function __construct(array $rules = [], $message = [], $ruleField = [])
    {
        parent::__construct($rules, $message, $ruleField);
    }

    // 自定义验证规则
    protected function occupancy($value,$rule,$data)
    {
        $where = [
            'appoint_time'=>strtotime($data['appoint_time']),
            'appoint_course'=>$data['appoint_course'],
            'classroom_model_id'=>$data['classroom_model_id']
        ];
        $course = model("course")->where($where)->find();
        if (!$course)
            return true;

        if ($course['appoint_promotion_model_id'] != $data['appoint_promotion_model_id'])
            return $course['classroom']['name']."在这个时间内已经被其它课程占用";
        if ($course['classroom']['customer_max'] == -1)
            return true;
        return $course['customer_count'] > $course['classroom']['customer_max']?"在这个时间内教室已经满员了":true;
    }

    // 自定义验证规则
    protected function checkPresell($value,$rule,$data)
    {
        $where = [
            'customer_model_id'=>$data['customer_model_id'],
            'package_model_id'=>$data['package_model_id']
        ];
        $uplift = model("uplift")->where($where)->find();
        if (!$uplift)
            return "还没有购买这个课程的课次";

        if ($uplift['amount'] == 0)
            return "课次数量不足";
        return true;
    }


    // 自定义验证规则
    protected function checkCustomer($value,$rule,$data)
    {
        $where = [
            'appoint_time'=>strtotime($data['appoint_time']),
            'appoint_course'=>$data['appoint_course'],
            'classroom_model_id'=>$data['classroom_model_id'],
            'customer_model_id'=>$data['customer_model_id']
        ];
        $provider = model("provider")->where($where)->find();
        return $provider?"在这个时间内已经安排课程了！(".$provider->promotion->name.")":true;
    }
}
