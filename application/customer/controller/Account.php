<?php

namespace app\customer\controller;

use app\common\library\Auth;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Account extends Customer
{
    public function __construct() {
        parent::__construct();
        $this->model = model("account");
    }

    public function index() {
        $provider = model("account")
            ->where(['reckon_type'=>'customer','reckon_model_id'=>$this->user->customer_model_id])
            ->order("createtime", "desc");
        $list = $provider->paginate(10, false, ['type' => '\\app\\common\\library\\Bootstrap']);;
        $this->view->assign("__PAGELIST__", $list);
        return $this->view->fetch();
    }
}
