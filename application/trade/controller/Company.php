<?php

namespace app\trade\controller;


/**
 * 业务管理
 *
 * @icon fa fa-circle-o
 */
class Company extends Trade
{
    protected $selectpageFields = ['name', 'idcode', 'id', 'status','business_licence','aptitude_state'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\trade\model\Company;
    }


    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("company.branch_model_id", $branch_model_id);

        return $model;
    }

}
