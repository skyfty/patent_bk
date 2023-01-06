<?php

namespace app\trade\controller;

use app\admin\library\Auth;
use app\common\controller\Backend;


/**
 * 后台控制器基类
 */
class Trade extends Backend
{
    /**
     * 权限控制类
     * @var Auth
     */
    protected $noNeedLogin = [];


    protected $searchFields = ['name', 'idcode'];

    protected $modelSceneValidate = true;

    protected $relationSearch =[];

    public function _initialize()
    {
      parent::_initialize();

    }

    protected function spectacle($model) {
        return $model;
    }

    protected function getDataLimitAdminIds() {
        return null;

    }

}
