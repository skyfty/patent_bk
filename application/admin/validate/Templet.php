<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Templet extends Cosmetic
{
    protected $name = "templet";

    /**
     * 验证规则
     */
    protected $rule = [
        'name'       => 'require',
    ];
    protected $scene = [
        'add'  => ['name'=>'require|token'],
    ];
    
}
