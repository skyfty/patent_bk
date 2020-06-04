<?php

namespace app\admin\controller;

use app\admin\model\Modelx;
use app\common\controller\Backend;
use think\App;

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

    use \app\admin\library\traits\Produce;


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
