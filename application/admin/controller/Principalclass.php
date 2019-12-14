<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 主体分类
 *
 * @icon fa fa-circle-o
 */
class Principalclass extends Cosmetic
{
    
    /**
     * Principalclass模型对象
     * @var \app\admin\model\Principalclass
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Principalclass;
    }

}
