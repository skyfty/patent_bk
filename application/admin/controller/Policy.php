<?php

namespace app\admin\controller;

/**
 * 政策管理
 *
 * @icon fa fa-circle-o
 */
class Policy extends Cosmetic
{
    
    /**
     * Policy模型对象
     * @var \app\admin\model\Policy
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Policy;
    }


    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("policy.branch_model_id", $branch_model_id);

        return $model;
    }
}
