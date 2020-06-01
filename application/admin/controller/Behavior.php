<?php

namespace app\admin\controller;

/**
 * 模板行为
 *
 * @icon fa fa-circle-o
 */
class Behavior extends Cosmetic
{
    
    /**
     * Behavior模型对象
     * @var \app\admin\model\Behavior
     */
    protected $model = null;
    protected $relationSearch = ["assembly"];

    use \app\admin\library\traits\Condition;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Behavior;
        $this->dataLimit = false;

    }

}
