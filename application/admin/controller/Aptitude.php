<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 资质管理
 *
 * @icon fa fa-circle-o
 */
class Aptitude extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Aptitude;
    }

    public function produce() {
        $row = $this->model->where("id",$this->request->param("id"))->find();
        if (!$row)
            $this->error(__('No Results were found'));

        $where = [];
        $procedure_ids = $this->request->param("procedure_ids/a");
        if ($procedure_ids) {
            $where['id']=["in",$procedure_ids ];
        }

        $procedures = model("procedure")->where("relevance_model_type","aptitude")->where($where)->select();
        foreach($procedures as $procedure) {
            $row->produceDocument($procedure);
        }
    }

    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("aptitude.branch_model_id", $branch_model_id);

        return $model;
    }
}
