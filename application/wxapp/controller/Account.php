<?php

namespace app\wxapp\controller;

use app\common\library\Auth;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Account extends Wxapp
{
    public function __construct() {
        parent::__construct();
        $this->model = model("account");
    }

    public function index() {

        return $this->view->fetch();
    }
}
