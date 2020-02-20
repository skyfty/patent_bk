<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 业务管理
 *
 * @icon fa fa-circle-o
 */
class Business extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Business;
    }


    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("business.branch_model_id", $branch_model_id);

        return $model;
    }

}
