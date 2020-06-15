<?php

namespace app\wxapp\controller;

use think\Db;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Branch extends Wxapp
{
    public function __construct() {
        parent::__construct();
        $this->model = model("branch");
    }


}
