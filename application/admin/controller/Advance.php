<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 业务进度
 *
 * @icon fa fa-circle-o
 */
class Advance extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Advance;
    }


}
