<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 著作权
 *
 * @icon fa fa-copyright
 */
class Invent extends Cosmetic
{
    
    /**
     * Copyright模型对象
     * @var \app\admin\model\Invent
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Invent;
    }

    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("invent.branch_model_id", $branch_model_id);

        return $model;
    }
}