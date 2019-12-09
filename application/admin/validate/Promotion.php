<?php

namespace app\admin\validate;

use think\Validate;
use app\common\validate\Cosmetic;

class Promotion extends Cosmetic
{
    protected $name = "promotion";

    protected $rule = [
        'name'  => 'require',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['name'=>'require|token'],
    ];
}
