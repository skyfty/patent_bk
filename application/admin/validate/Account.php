<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Account extends Cosmetic
{
    protected $name = "account";

    protected $rule = [
        'cheque_model_id'  =>  'require|number|checkCheque',
        'reckon_type'  =>  'checkReckonType',
        'money'  =>  'require|float|token',
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        parent::__construct($rules, $message, $field);
    }

    protected function checkReckonType($value,$rule,$data, $field, $title)
    {
        $cheque = \app\common\model\Cheque::get(['reckon_table' => $value]);
        return $cheque ? true : $title.'错误';
    }

    protected function checkCheque($value,$rule,$data, $field, $title)
    {
        $cheque = \app\common\model\Cheque::get($data['cheque_model_id']);
        if (!$cheque)
            return '无效的类目类型';

        if ($cheque->id == 47 && $data['money'] > 0) {
            $reckon = model($cheque['reckon_table'])->get($data['reckon_model_id']);
            if (!$reckon)
                return '无效的操作对象';
            if ($reckon->lucre_balance < $data['money'])
                return '收益账户余额不足';
        }
        return true;
    }
}
