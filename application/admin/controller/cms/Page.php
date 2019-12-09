<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;

/**
 * 单页表
 *
 * @icon fa fa-circle-o
 */
class Page extends Backend
{

    /**
     * Page模型对象
     */
    protected $model = null;
    protected $noNeedRight = ['selectpage_type'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Page');
        $this->view->assign("flagList", $this->model->getFlagList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
}
