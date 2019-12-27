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
        $list = $this->model->with($this->relationSearch)->field("id,principalclass_model_id")->where("id","in", function($query){
            $query->table("__CLAIM__")->where("customer_model_id", $this->user->customer->id)->field("principal_model_id");
        })->order("principalclass_model_id asc")->select();
        if (count($list) <= 0) {
            $this->view->assign("jumpurl", "/user/adviser");
            return $this->view->fetch("noprincipal");
        }
        $this->view->assign("list", $list);
        $id = $this->request->param("id", $list[0]['id']);
        $this->view->assign("id", $id);
        $this->view->assign("row", $this->model->find($id));

        return $this->view->fetch();
    }

    public function lore() {

    }
}
