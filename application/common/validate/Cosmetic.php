<?php

namespace app\common\validate;

use app\admin\model\Fields;
use app\admin\model\Scenery;
use think\Validate;

class Cosmetic extends Validate
{
    protected $field = [
    ];

    public function __construct(array $rules = [], $message = [], $ruleField = [])
    {
        $fields = [];
        foreach(array_keys($this->rule) as $v) {
            if (is_numeric($v))
                continue;

            $field = $v;
            $pos = strrpos($field, '_model_id');
            if ($pos !== false) {
                $field = substr($v, 0, $pos);
            }
            $pos = strrpos($field, '_cascader_id');
            if ($pos !== false) {
                $field = substr($v, 0, $pos);
            }
            $modelField = Fields::get(['name'=>$field, 'model_table'=>$this->name]);
            if ($modelField) {
                $fields[$v] = $modelField->title;
            }
        }
        parent::__construct($rules, $message, array_merge($ruleField, $fields));

        $scene = Scenery::where(['model_table'=>$this->name, 'type'=>'default', 'pos'=>'view'])->column("name, fields");
        foreach($scene as $k=>&$v) {
            $v = explode(",", $v);
            if (isset($this->scene[$k])) {
                $v = array_merge($v, $this->scene[$k]);
                unset($this->scene[$k]);
            }
        }

        if (isset($this->scene["add"]) && isset($scene['view'])) {
            $this->scene["add"] = array_merge($scene['view'], $this->scene["add"]);
        }
        parent::scene(array_merge_recursive($scene, $this->scene));
    }
}
