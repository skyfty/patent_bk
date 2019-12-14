<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Principalclass extends Cosmetic
{
    protected $name = "principalclass";

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
