<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 服务类型
 *
 * @icon fa fa-circle-o
 */
class Species extends Cosmetic
{
    
    /**
     * Species模型对象
     * @var \app\admin\model\Species
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Species;
    }

}
