<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Shuttering extends Cosmetic
{
    protected $name = "shuttering";

    protected $rule = [
        'name'  =>  'require',
    ];
    protected $scene = [
        'add'  => [
            'name',
        ]
    ];
}
