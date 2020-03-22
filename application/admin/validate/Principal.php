<?php

namespace app\admin\validate;

use app\common\validate\Cosmetic;

class Principal extends Cosmetic
{
    protected $name = "principal";

    protected $rule = [
        'name'       => 'require|checkPrincipal'
    ];


    /**
     * 验证场景
     */
    protected $scene = [
        'add'  =>['name'],
        'edit' => ['name'],
    ];

    public function checkPrincipal($value,$rule,$data, $field, $title) {

        if ($data['principalclass_model_id']  == 2) {
            $principal = model("Principal")->where("name", $data['name'])->where(function($query)use($data){
                if (isset($data['id'])) {
                    $query->where("id", "neq", $data['id']);
                }
            })->find();
            if ($principal) {
                return "此主体名称已经存在了";
            }
        }
        return true;
    }
}
