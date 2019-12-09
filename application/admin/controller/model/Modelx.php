<?php

namespace app\admin\controller\model;

use app\common\controller\Backend;

/**
 * 内容模型表
 *
 * @icon fa fa-circle-o
 */
class Modelx extends Backend
{

    /**
     * Model模型对象
     */
    protected $model = null;
    protected $beforeActionList = [
    ];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Modelx');
        $this->view->assign("statusList", $this->model->getStatusList());
    }

}
