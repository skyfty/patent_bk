<?php

namespace app\admin\validate;

use think\Validate;
use app\common\validate\Cosmetic;

class Claim extends Cosmetic
{
    protected $name = "claim";

    protected $rule = [
        'principal_model_id'       => 'require|checkexist|checktype',
        'customer_model_id'       => 'require|checkexist|checktype',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [
            'principal_model_id',
            'customer_model_id',

        ],
    ];


    // 自定义验证规则
    protected function checkexist($value,$rule,$data) {
        $where = [
            'principal_model_id'=>$data['principal_model_id'],
            'customer_model_id'=>$data['customer_model_id'],
        ];
        $amount = model("claim")->where($where)->count();
        return $amount>0?"这个认领已经存在了":true;
    }


    // 自定义验证规则
    protected function checktype($value,$rule,$data) {
        $principal = model("principal")->get($data['principal_model_id']);
        if ($principal['principalclass_model_id'] != "1") {
            return true;
        }
        $amount = model("claim")->with("principal")->where("claim.customer_model_id", $data['customer_model_id'])->where("principal.principalclass_model_id", 1)->count();
        return $amount>0?"只可以有一个人人主体":true;
    }
}

