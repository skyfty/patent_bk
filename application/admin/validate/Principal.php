<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Principal extends Cosmetic
{
    protected $name = "principal";

    protected $rule = [
    ];


    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [
        ],
        'view.edit' => [
        ]
    ];

}
