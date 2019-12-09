<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 公告表
 *
 * @icon fa fa-circle-o
 */
class Note extends Backend
{
    
    /**
     * Note模型对象
     * @var \app\admin\model\Note
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Note;
        $this->view->assign("flagList", $this->model->getFlagList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    public function view($ids=null) {
        $row = $this->model->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);

        return $this->view->fetch();
    }
}
