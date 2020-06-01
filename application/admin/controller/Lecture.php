<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 授课流程
 *
 * @icon fa fa-circle-o
 */
class Lecture extends Cosmetic
{
    
    /**
     * Lecture模型对象
     * @var \app\admin\model\Lecture
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Lecture;
        $this->view->assign("uniqueList", $this->model->getUniqueList());
        $this->dataLimit = false;

    }

    public function edit($ids = NULL) {
        if ($this->request->isPost()) {
            return parent::edit($ids);
        }

        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    public function classtree() {
        $chequelList = [];
        $id = $this->request->param("id");
        if ($id === null) {
            $chequelList[] = [
                'id'     => 0,
                'isParent'=>true,
                'name'   => "流程模板",
                'open'  => true,
                'childOuter'=>false,
                'status'=>"locked",
                'iconSkin'=>"catenate",
            ];
        }
        $list = $this->model->where('pid', $id)->where("type", 'catenate')->select();
        foreach ($list as $k => $v) {
            $chequelList[] = $v->tree_node;
        }
        return $chequelList;
    }

    protected function enumlecture($lecatenate, &$chequelList) {
        $lectures = model("lecture")->where("pid", $lecatenate['id'])->where('type','lecture')->select();
        foreach($lectures as $v2) {
            $lectureitem = $v2->tree_node;
            $chequelList[] = $lectureitem;
        }
    }

    protected function enumtree($pid = 0, &$chequelList) {
        $list = $this->model->where('pid', $pid)->where('type','catenate')->select();
        foreach ($list as $k => $v) {
            $lecatenateitem =  $v->tree_node;
            $lecatenateitem['children'] = [];
            $this->enumtree($v['id'], $lecatenateitem['children']);
            $this->enumlecture($v, $lecatenateitem['children']);
            $chequelList[] = $lecatenateitem;
        }
    }

    public function alltree($pid = 0) {
        $chequelList =  [];
        $this->enumtree($pid, $chequelList);
        $this->enumlecture($this->model->where('id', $pid)->find(), $chequelList);
        return $chequelList;
    }
}
