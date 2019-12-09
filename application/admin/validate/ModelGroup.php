<?php

namespace app\admin\validate;

use think\Validate;

class ModelGroup extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'model_type'       => 'require',
        'title'     => 'require',
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
        'add'  => [
            'model_type', 'title'
        ],
        'edit' => [
            'title'
        ],
    ];
}
