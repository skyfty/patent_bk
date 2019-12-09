<?php

namespace app\admin\validate;

use think\Validate;

class Sight extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'scenery_id|场景ID' => 'require|integer',
        'model_id|模型ID' => 'require|integer',
        'fields_id|字段ID' => 'require|integer',
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['scenery_id','model_id','fields_id'],
        'edit' => ['scenery_id','model_id','fields_id'],
    ];
    
}
