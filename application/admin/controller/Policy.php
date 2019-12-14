<?php

namespace app\admin\controller;

/**
 * 政策管理
 *
 * @icon fa fa-circle-o
 */
class Policy extends Cosmetic
{
    
    /**
     * Policy模型对象
     * @var \app\admin\model\Policy
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Policy;
    }
}
