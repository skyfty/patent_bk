<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Policy extends Cosmetic
{
    protected $name = "policy";

    protected $rule = [
    ];


    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [
        ],
        'view.edit' => []
    ];

}
