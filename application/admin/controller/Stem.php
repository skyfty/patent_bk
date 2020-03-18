<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 政策来源
 *
 * @icon fa fa-circle-o
 */
class Stem extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Stem;
    }

}
