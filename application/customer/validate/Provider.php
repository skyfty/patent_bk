<?php

namespace app\customer\validate;

use think\Validate;

class Provider extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'branch_model_id' => 'require',
        'appoint_time' => 'require',
        'appoint_promotion_model_id' => 'require',
    ];

    /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 字段描述
     */
    protected $field = [
        'branch_model_id' => '预约门店',
        'appoint_time' => '预约时间',
        'appoint_promotion_model_id' => '预约项目',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['branch_model_id', 'appoint_time', 'appoint_promotion_model_id'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        parent::__construct($rules, $message, $field);
    }

}
