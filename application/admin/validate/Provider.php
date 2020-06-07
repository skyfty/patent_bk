<?php

namespace app\admin\validate;

use think\Validate;
use app\common\validate\Cosmetic;

class Provider extends Cosmetic
{
    protected $name = "provider";

    protected $rule = [
        'promotion_model_id'       => 'require',
    ];

    protected $scene = [
        'add'  => [
            'promotion_model_id',
            'customer_model_id'=>'require|token',
        ],
    ];

    public function __construct(array $rules = [], $message = [], $ruleField = [])
    {
        parent::__construct($rules, $message, $ruleField);
    }

}
