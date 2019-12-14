<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Industry extends Cosmetic
{
    protected $name = "industry";

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
