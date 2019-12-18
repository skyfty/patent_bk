<?php

namespace app\customer\controller;
use app\common\model\Fields;


/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Principal extends Customer
{
    protected $layout = 'principal/layout';

    // 初始化
    public function __construct()
    {
        parent::__construct();
        $this->model = model('principal');
    }

    public function index() {
        $list = $this->model->with($this->relationSearch)->field("id,name")->where("id","in", function($query){
            $query->table("__CLAIM__")->where("customer_model_id", $this->user->customer->id)->field("principal_model_id");
        })->select();
        if (count($list) <= 0) {
            $this->view->assign("jumpurl", "/customer/adviser");
            return $this->view->fetch("nostudent");
        }
        $this->view->assign("list", $list);

        $id = $this->request->param("id", $list[0]['id']);
        $this->view->assign("id", $id);
        $customer = $this->model->with($this->relationSearch)->find($id);
        $this->view->assign("row", $customer);

        return $this->view->fetch();
    }

    public function lore() {

    }
}
