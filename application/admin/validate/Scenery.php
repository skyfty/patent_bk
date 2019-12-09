<?php

namespace app\admin\validate;

use think\Validate;

class Scenery extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name|名称'       => 'require',
        'title|管理员'     => 'require',
        'model_id|模型ID' => 'require|integer',
        'status|状态'     => 'require|in:normal,hidden,locked',
        'pos|位置'     => 'require|in:index,view',
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
        'add'  => [
            'name', 'title', 'model_id', 'status', 'pos'
        ],
        'edit' => [
            'name', 'title', 'model_id', 'status', 'pos'
        ],
    ];
    
}
