<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;

use fast\Tree;

trait Cascader
{
    public function cascader() {
        $name = self::class;
        $name = md5($name . "cascader");
        $filedir = CACHE_PATH . 'cascader';
        $filepath = $filedir . DS . $name;

        if (file_exists($filepath)) {
            $list = include $filepath;
        } else {
            $list = $this->cascaderTree(0);
            if (!file_exists($filedir)) {
                mkdir($filedir);
            }
            file_put_contents($filepath, '<?php' . "\n\nreturn " . var_export($list, true) . ";");
        }
        return json($list);
    }

    protected function cascaderTree($pid) {
        $list = [];
        foreach($this->model->order('id desc')->where('pid', $pid)->select() as $k=>$v) {
            $data = [
                'value'=>$v->id,
                'label'=>$v->name,
                'model'=>$v->model,
            ];
            $children = $this->cascaderTree($v['id']);
            if ($children && count($children) > 0) {
                $data['children'] = $this->cascaderTree($v['id']);
            }
            $list[] = $data;
        }
        return $list;
    }
}