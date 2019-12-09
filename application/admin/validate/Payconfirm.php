<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Payconfirm  extends Account
{

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        parent::__construct($rules, $message, $field);
    }


    protected function checkReckonType($value,$rule,$data, $field, $title)
    {
       return true;
    }
}
