<?php

namespace app\admin\controller;

/**
 * 认领管理
 *
 * @icon fa fa-circle-o
 */
class Claim extends Cosmetic
{
    
    /**
     * Claim模型对象
     * @var \app\admin\model\Claim
     */
    protected $model = null;
    protected $selectpageShowFields = ['name', 'idcode'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Claim;
    }

    public function add() {
        if (!$this->request->isPost()) {
            $genearch_model_id = $this->request->param("genearch_model_id");
            $this->view->assign("genearch", $genearch_model_id?model("genearch")->field("id, name, branch_model_id")->where("id",$genearch_model_id)->find():null);
            $customer_model_id = $this->request->param("customer_model_id");
            $this->view->assign("customer", $customer_model_id?model("customer")->field("id, name, branch_model_id")->where("id",$customer_model_id)->find():null);
        }
        return parent::add();
    }

}
