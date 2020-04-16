<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Alternating extends Cosmetic
{
    protected $name = "alternating";

    /**
     * 验证规则
     */
    protected $rule = [
        'name'  =>  'require|checkName',

    ];
    public function __construct(array $rules = [], $message = [], $field = [])
    {
        parent::__construct($rules, $message, $field);
    }

    protected function checkName($value,$rule,$data, $field, $title)
    {
        if (isset($data['id'])) {
            $alternating = model("alternating")->get($data['id']);
            $cnt = model("alternating")
                ->where("name", $data['name'])
                ->where("id", "<>", $data['id'])
                ->where("procedure_model_id", $alternating['procedure_model_id'])->count();
            if ($cnt != 0) {
                return "模板变量已经存在";
            }
        } else {
            $cnt = model("alternating")
                ->where("name", $data['name'])
                ->where("procedure_model_id", $data['procedure_model_id'])->count();
            if ($cnt != 0) {
                return "模板变量已经存在";
            }
        }
        return true;
    }
}
