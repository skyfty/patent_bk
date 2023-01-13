<?php

namespace app\trade\controller;

use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\trade\model\Fields;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */

class Customer extends Trade
{
    protected $selectpageFields = ['name', 'idcode', 'slug', 'id', 'status'];
    protected $searchFields = ['name', 'idcode', 'slug'];
    protected $selectpageShowFields = ['name', 'idcode'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("customer");
        $this->assignFields();
    }

    protected function assignFields() {
        $scenery = Scenery::get(['model_table' => "customer",'pos'=>'index'],[],true);
        $fields =  Sight::with('fields')->where(['scenery_id'=>$scenery['id']])->order("weigh", "asc")->cache(true)->select();;
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

        $scenery = Scenery::get(['model_table' => "persion",'pos'=>'index'],[],true);
        $personal_fields =  Sight::with('fields')->where(['scenery_id'=>$scenery['id']])->order("weigh", "asc")->cache(true)->select();;
        $this->view->assign('personal_fields', $personal_fields);

        $scenery = Scenery::get(['model_table' => "company",'pos'=>'index'],[],true);
        $company_fields =  Sight::with('fields')->where(['scenery_id'=>$scenery['id']])->order("weigh", "asc")->cache(true)->select();;
        $this->view->assign('company_fields', $company_fields);

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
