<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 业务流程
 *
 * @icon fa fa-circle-o
 */
class Plan extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Plan;
    }

    protected function spectacle($model) {

        return $model;
    }
}
