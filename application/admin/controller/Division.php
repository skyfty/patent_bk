<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 大纲管理
 *
 * @icon fa fa-circle-o
 */
class Division extends Cosmetic
{
    
    /**
     * Division模型对象
     * @var \app\admin\model\Division
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Division;
    }


    protected function spectacle($model) {
        return $model;
    }

}
