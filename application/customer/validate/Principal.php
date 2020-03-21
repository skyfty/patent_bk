<?php

namespace app\customer\validate;

use think\Validate;

class Principal extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name' => 'require|unique:principal'
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
        'name' => '主体名称'
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['name'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        parent::__construct($rules, $message, $field);
    }

}
