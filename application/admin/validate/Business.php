<?php

namespace app\admin\validate;

use think\Validate;
use app\common\validate\Cosmetic;

class Business extends Cosmetic
{
    protected $name = "business";

    /**
     * 验证规则
     */
    protected $rule = [
        'name'       => 'require',
    ];
    protected $scene = [
        'add'  => ['sum_settle_price'=>'require|token'],
    ];
}
