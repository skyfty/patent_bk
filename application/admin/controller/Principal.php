<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 用户主体
 *
 * @icon fa fa-circle-o
 */
class Principal extends Cosmetic
{
    
    /**
     * Principal模型对象
     * @var \app\admin\model\Principal
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Principal;
    }

}
