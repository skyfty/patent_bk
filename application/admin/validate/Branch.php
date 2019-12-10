<?php

namespace app\admin\validate;

use think\Validate;
use app\common\validate\Cosmetic;

class Branch extends Cosmetic
{
    protected $name = "branch";

    protected $rule = [
        'name'       => 'require|unique:branch,name',
    ];


    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [
            'name'=>'require|unique:branch'
        ],
        'view.edit' => []
    ];
}
