<?php

namespace app\admin\controller;

use app\admin\model\Modelx;
use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\common\controller\Backend;
use think\App;
use think\Hook;

/**
 * 著作权
 *
 * @icon fa fa-copyright
 */
class Copyright extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Copyright;
    }

    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("copyright.branch_model_id", $branch_model_id);

        return $model;
    }


    public function code() {
        if (!$this->auth->check("copyright/code")) {
            Hook::listen('admin_nopermission', $this);
            $this->error(__('You have no permission'), '');
        }
        $ids =$this->request->param("ids", null);
        if ($ids === null)
            $this->error(__('Params error!'));
        $this->view->assign("row", $this->getModelRow($ids));

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

        } else {
            return $this->view->fetch();
        }
    }
}
