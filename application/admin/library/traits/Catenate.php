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

trait Catenate
{
    protected function enumAllChildNode($pid, &$ids) {
        $cids = $this->model->where("pid", $pid)->column("id");
        foreach($cids as $v) {
            $ids[] = $v;
            $this->enumAllChildNode($v, $ids);
        }
    }

    public function del($id = "") {
        $ids = [$id];
        $this->enumAllChildNode($id, $ids);
        $this->model->where("id", 'in',$ids)->delete();
        $this->success();
    }


    protected function enumParentNode($id, $pid) {
        $wareranges = [];
        while(true) {
            if ($id == $pid)
                break;
            $warerange = model("warerange")->get($id);
            if (!$warerange)
                break;
            $wareranges[] = $warerange;
            $id = $warerange['pid'];
        }
        return array_reverse($wareranges);
    }

    public function weigh() {
        $nodes = $this->request->param("nodes/a");
        foreach($nodes as $v) {
            foreach($v as $k=>$v2) {
                $weigh = $k + 1;
                $this->model->where("id", $v2)->update(['weigh'=>$weigh]);
            }
        }
    }

}