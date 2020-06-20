<?php


if (!function_exists('selectfield')) {

    /**
     * 生成单选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function pickerfield($model, $name)
    {
        $field = model("fields")->cache(true)->where("model_table", $model)->where("name", $name)->find();
        if (!$field) {
            return [];
        }
        $data = [];
        foreach($field->content_list as $k=>$v) {
            $data[] = ["id"=>$k, "name"=>$v];
        }
        return $data;
    }
}
