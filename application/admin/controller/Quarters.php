<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 岗位管理
 *
 * @icon fa fa-circle-o
 */
class Quarters extends Cosmetic
{
    
    /**
     * Quarters模型对象
     * @var \app\admin\model\Quarters
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Quarters;
    }
}
