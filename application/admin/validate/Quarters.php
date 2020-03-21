<?php

namespace app\admin\validate;

use app\admin\model\Fields;
use app\common\validate\Cosmetic;

class Quarters extends Cosmetic
{
    protected $name = "quarters";

    protected $rule = [
        'place'       => 'require|checkRepetition',
        'principal_model_id'       => 'require',
        'customer_model_id'       => 'require',
    ];

    protected $scene = [
        'add'  => [
            'place',
            'principal_model_id',
            'customer_model_id'=>'require',
        ],
    ];

    private function placeName($value) {
        $content_list = Fields::get(['model_table'=>'quarters','name'=>'place'])->content_list;
        return $value && isset($content_list[$value]) ? $content_list[$value] : $value;
    }

    protected function checkRepetition($value,$rule,$data, $field, $title)
    {
        $quarters = model("quarters")
            ->where("principal_model_id", $data['principal_model_id'])
            ->where("place", $data['place'])->find();
        if ($quarters != null) {
            return "不能有两个".$this->placeName($data['place'])."职位";
        }
        return true;
    }

    public function __construct(array $rules = [], $message = [], $ruleField = [])
    {
        parent::__construct($rules, $message, $ruleField);
    }
}
