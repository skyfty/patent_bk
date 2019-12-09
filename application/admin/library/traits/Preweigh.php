<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;

use fast\Tree;

trait Preweigh
{
    public function weigh() {
        $nodes = $this->request->param("nodes/a");
        $this->sort($nodes);
    }

    protected function sort($nodes) {
        foreach($nodes as $v) {
            foreach($v as $k=>$v2) {
                $weigh = $k + 1;
                $this->model->where("id", $v2)->update(['weigh'=>$weigh]);
            }
        }
    }
}