<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 业务文档模板
 *
 * @icon fa fa-circle-o
 */
class Shuttering extends Cosmetic
{
    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, ['topdf']);

        parent::_initialize();
        $this->model = new \app\admin\model\Shuttering;
    }

}
