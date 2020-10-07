<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Shuttering extends Cosmetic
{
    protected $name = "shuttering";

    protected $rule = [
        'name'  =>  'require',
        'procedure_model_id'=>'require',

    ];
    protected $scene = [
        'add'  => [
            'name',
            'procedure_model_id',
        ]
    ];
    protected $msg = [
        'procedure_model_id.require' => '业务步骤必须',
    ];
}
