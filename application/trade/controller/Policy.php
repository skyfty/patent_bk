<?php

namespace app\trade\controller;

use think\Config;

/**
 * 政策管理
 *
 * @icon fa fa-circle-o
 */
class Policy extends Trade
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\trade\model\Policy;
    }

    protected function spectacle($model) {
//        $branch_model_id = $this->request->param("branch_model_id");
//        if ($branch_model_id == null) {
//            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
//                return $model;
//            }
//        }
//        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;
//
//        $model->where("policy.branch_model_id", $branch_model_id);

        return $model;
    }

    public function rest($ids = NULL) {
        $row = $this->model->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $row->match();
        $this->success();

    }
}
