<?php

namespace app\admin\validate;

use think\Validate;

class Log extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'model_type'       => 'require',
        'model_id'     => 'require',
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
    ];
}
