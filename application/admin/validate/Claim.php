<?php

namespace app\admin\validate;

use think\Validate;
use app\common\validate\Cosmetic;

class Claim extends Cosmetic
{
    protected $name = "claim";

    protected $rule = [
        'genearch_model_id'       => 'require|checkexist',
        'customer_model_id'       => 'require|checkexist',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [
            'genearch_model_id',
            'customer_model_id',

        ],
    ];


    // 自定义验证规则
    protected function checkexist($value,$rule,$data) {
        $where = [
            'genearch_model_id'=>$data['genearch_model_id'],
            'customer_model_id'=>$data['customer_model_id'],
        ];
        $amount = model("claim")->where($where)->count();
        return $amount>0?"这个认领已经存在了":true;
    }
}

