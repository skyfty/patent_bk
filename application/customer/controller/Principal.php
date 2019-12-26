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
        $list = $this->model->with($this->relationSearch)->where("id","in", function($query){
            $query->table("__CLAIM__")->where("customer_model_id", $this->user->customer->id)->field("principal_model_id");
        })->select();
        if (count($list) <= 0) {
            $this->view->assign("jumpurl", "/customer/adviser");
            return $this->view->fetch("nostudent");
        }
        $this->view->assign("list", $list);

        $default_id = $list[0]['id'];
        foreach($list as $li) {
            if ($li['principalclass_model_id'] == 1) {
                $default_id = $li->id;
                break;
            }
        }

        $id = $this->request->param("id", $default_id);


        $this->view->assign("id", $id);
        $principal= $this->model->find($id);
        $this->view->assign("row", $principal);

        return $this->view->fetch();
    }

    public function lore() {

    }
}
