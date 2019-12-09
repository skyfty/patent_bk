<?php

namespace app\customer\validate;

use think\Validate;

class Customer extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'bankname' => 'require',
        'bankdepositname' => 'require',
        'banknumber' => 'require',
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
        'bankname' => '开户行',
        'bankdepositname' => '开户名',
        'banknumber' => '账号',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'bank' => ['bankname', 'bankdepositname', 'banknumber'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        parent::__construct($rules, $message, $field);
    }

}
