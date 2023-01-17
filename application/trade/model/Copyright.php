<?php

namespace app\trade\model;

use think\Model;
use app\admin\library\Auth;

class Copyright extends   \app\common\model\Copyright
{
    // 追加属性
    protected $append = [
        "develop_os",
        "develop_tool",
        "auxiliary_software",
        "running_software",
        "dlanguage",
        "company",
        "format_info"
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }

    public function getFormatInfoAttr() {
        $result = [];
        $fields = model("fields")->where("name", "in", ["name","simple_name","version","category","purpose"])->where("model_table", "copyright")->select();
        foreach ($fields as $k=>$field) {
            $result[] = ["value"=>$this[$field['name']], "title"=>$field['title']];
        }
        $result[] = ['value'=>$this->getPublishAttrText(), "title"=>'是否发表'];
        return $result;
    }
}
