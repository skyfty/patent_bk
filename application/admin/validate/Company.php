<?php

namespace app\admin\validate;

use think\Validate;

class Company extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        "english_name"  => 'length:3,4|regex:^[A-Z]+$'
    ];
    protected $message  =   [
        'english_name.length'     => '名称最多不能超过4个字符',
        'english_name.regex'   => '名称必须是3到4个大写字母组成'
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
    
}
