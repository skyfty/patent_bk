<?php

namespace app\trade\controller;

use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\common\controller\Trade;
use think\App;
use think\Exception;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */

class Principal extends Trade
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("Principal");

    }

    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("customer.branch_model_id", $branch_model_id);

        return $model;
    }

    /**
     * 添加
     */
    public function add() {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if (!$params) {
                $this->error(__('Parameter %s can not be empty', ''));
            }

            $db = $this->model->getQuery();
            $db->startTrans();
            try {
                $result = $this->model->allowField(true)->save($params);
                if (!$result) {
                    throw new Exception("error");
                }
                $result = model($this->model['substance_type'])->allowField(true)->save($params, ['id'=>$this->model['substance_id']]);
                if (!$result) {
                    throw new Exception("error");
                }
                model("claim")->save([
                    "principal_model_id"=>$this->model['id'],
                    "customer_model_id"=>input("param.customer_id"),
                ]);
                $db->commit();
                $this->success("", null, $this->model->get($this->model->id)->toArray());
            } catch (\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        }
        return $this->view->fetch();
    }
    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost()) {
            parent::edit($ids);
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
