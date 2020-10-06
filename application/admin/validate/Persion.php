<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Persion extends Cosmetic
{
    protected $name = "persion";

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
