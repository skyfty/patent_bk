<?php

namespace app\admin\validate;

use think\Validate;

class Persion extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
    ];
    protected $message  =   [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
    
}
