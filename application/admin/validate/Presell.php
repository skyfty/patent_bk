<?php

namespace app\admin\validate;

use think\Validate;
use app\common\validate\Cosmetic;

class Presell extends Cosmetic
{
    protected $name = "presell";

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
