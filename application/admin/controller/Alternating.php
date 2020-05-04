<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 步骤变量
 *
 * @icon fa fa-circle-o
 */
class Alternating extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Alternating;
    }
}
