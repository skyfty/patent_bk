<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 课件模板
 *
 * @icon fa fa-circle-o
 */
class Templet extends Cosmetic
{
    
    /**
     * Templet模型对象
     * @var \app\admin\model\Templet
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Templet;
        $this->dataLimit = false;

    }
}
