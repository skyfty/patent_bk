<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Warehouse extends Cosmetic
{
    protected $name = "package";

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
