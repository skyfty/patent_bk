<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 著作权申请人
 *
 * @icon fa fa-circle-o
 */
class Crproposer extends Cosmetic
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Crproposer;
    }
}
