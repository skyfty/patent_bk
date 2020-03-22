<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Principal extends Cosmetic
{
    protected $name = "principal";

    protected $rule = [
        'name'       => 'require|unique:principal'
    ];


    /**
     * 验证场景
     */
    protected $scene = [
        'add'  =>['name'],
        'edit' => ['name'],
    ];

}
