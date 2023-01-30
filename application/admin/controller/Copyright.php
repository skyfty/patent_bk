<?php

namespace app\admin\controller;

use app\admin\model\Fields;
use app\admin\model\Modelx;
use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\common\controller\Backend;
use fast\Random;
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
    use \app\admin\library\traits\Produce;
    use \app\common\library\traits\Procshutter;
    use \app\common\library\traits\Produce;

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

    public function code($ids) {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error('未找到对应模型');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $result = $row->saveCode($params['code']);
                if ($result !== false) {
                    $this->result($this->getModelRow($ids),1);
                } else {
                    $this->error($row->getError());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    public function applicant() {
        $ids =$this->request->param("ids", null);
        if ($ids === null)
            $this->error(__('Params error!'));
        $this->view->assign("row", $this->getModelRow($ids));
        return $this->view->fetch();
    }

    use \app\common\library\traits\SyncCompany;

}
