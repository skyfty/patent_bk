<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;
use app\common\model\Fields;

use fast\Tree;

trait Condition
{
    public function condition($id) {
        $row = $this->model->get($id);
        if (!$row) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $fields = [];

        $field = [];
        $field['name']= "condition";
        $field['title']= "环境";
        $field['newstatus']= $field['editstatus'] = ($row['inside']?'locked':'normal');
        $field['content']= $row['content'];
        $field['defaultvalue']= $row['content'];
        $field['type']= $row['template'];
        if (in_array($row['template'],['select','selects'])) {
            $field['content_list'] = \app\common\model\Config::decode($row['content']);
        }
        $fields[] = $field;

        $field = \app\admin\model\Fields::get(['model_table'=>'preset','name'=>'status'],[],true);
        $field['newstatus']= $field['editstatus'] = ($row['inside']?'locked':'normal');
        $field['content_list'] = \app\common\model\Config::decode($field['content']);
        $field['defaultvalue']= ($row['inside']?'0':'1');
        $fields[] = $field;

        $adjectives = json_decode($row['adjective'],true);
        if ($adjectives) {
            foreach($adjectives as $fileName=>$field) {
                $field['newstatus']= $field['editstatus'] = 'normal';
                if (in_array($field['type'],['select','selects'])) {
                    $field['content_list'] = $field['content'];
                }
                $field['name'] = "adjective/" .$field['name'];
                $fields[] = $field;
            }
        }

        return $fields;
    }
}