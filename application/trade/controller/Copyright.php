<?php

namespace app\trade\controller;

use app\admin\model\Scenery;
use app\admin\model\Sight;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */

class Copyright extends Trade
{
    protected $selectpageFields = ['name', 'idcode', 'slug', 'id', 'status'];
    protected $searchFields = ['name', 'idcode', 'slug'];
    protected $selectpageShowFields = ['name', 'idcode'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("copyright");
        $this->assignFields();
    }

    protected function assignFields() {
        $scenery = Scenery::get(['model_table' => "copyright",'pos'=>'view'],[],true);
        $fields =  Sight::with('fields')->where(['scenery_id'=>$scenery['id']])->where("fields.name", "not in",[
            "branch",
        ])->order("weigh", "asc")->cache(true)->select();;
        $this->view->assign('fields', $fields);
    }

    /**
     * 添加
     */
    public function add() {
        if (!$this->request->isPost()) {
            $row['company_model_id'] = input("param.company_model_id");
            $this->view->assign("row", $row);
        }
        return parent::add();
    }


    public function code($ids = null) {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error('未找到对应模型');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $result = $row->saveCodeFile($params['code']);
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error($row->getError());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
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
}
