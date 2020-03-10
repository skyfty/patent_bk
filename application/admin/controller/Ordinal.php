<?php

namespace app\admin\controller;

/**
 * 政策规则
 *
 * @icon fa fa-circle-o
 */
class Ordinal extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Ordinal;
    }


    /**
     * 添加
     */
    public function add() {
        if (!$this->request->isPost() ) {

        }
        return parent::add();
    }
}
