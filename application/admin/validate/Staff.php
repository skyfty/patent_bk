<?php

namespace app\admin\validate;

use think\Validate;
use app\common\validate\Cosmetic;

class Staff extends Cosmetic
{
    protected $name = "staff";

    protected $rule = [
        'admin_name'=>'checkAdminName',
        'name'       => 'require',
    ];

    protected $scene = [
        'add'  => ['admin_name', 'name'=>'require'],
    ];

    public function checkAdminName($value,$rule,$data, $field, $title) {
        return validate('Admin')->scene("staff")->check(['username'=>$value]);
    }
}
