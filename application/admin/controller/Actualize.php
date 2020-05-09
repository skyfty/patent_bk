<?php

namespace app\admin\controller;

/**
 * 相关政策
 *
 * @icon fa fa-circle-o
 */
class Actualize extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Actualize;
    }

}
