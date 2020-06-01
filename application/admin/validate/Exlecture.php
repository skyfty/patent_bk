<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Exlecture extends Cosmetic
{
    protected $name = "exlecture";

    protected $rule = [
        'lecture_id'  =>  'lecture',
    ];

    protected $scene = [
        'add'  => [
            'lecture_id',
        ]
    ];

    protected function lecture($value,$rule,$data, $field, $title) {
        $lecture = \app\admin\model\Lecture::get($data['lecture_id']);
        if (!$lecture)
            return '无效的类型';
        if (!$lecture['unique']) {
            return true;
        }
        $where = [
            'lecture_id'=>$data['lecture_id'],
            'promotion_model_id'=>$data['promotion_model_id'],
        ];
        $amount = model("exlecture")->where($where)->count();
        return $amount>0?$lecture['name']."课件已经存在了":true;
    }
}
