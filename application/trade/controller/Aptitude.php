<?php

namespace app\trade\controller;

use app\admin\model\Scenery;
use app\admin\model\Sight;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */

class Aptitude extends Trade
{
    protected $selectpageFields = ['name', 'idcode', 'slug', 'id', 'status'];
    protected $searchFields = ['name', 'idcode', 'slug'];
    protected $selectpageShowFields = ['name', 'idcode'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("aptitude");
        $this->assignFields();
    }

    protected function assignFields() {
        $scenery = Scenery::get(['model_table' => "aptitude",'pos'=>'view'],[],true);
        $fields =  Sight::with('fields')->where(['scenery_id'=>$scenery['id']])->where("fields.name", "not in",[
            "branch",
        ])->order("weigh", "asc")->cache(true)->select();;
        $this->view->assign('fields', $fields);
    }

    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            return parent::index();
        }
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
