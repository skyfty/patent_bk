<?php

namespace app\admin\controller;

/**
 * 政策方案
 *
 * @icon fa fa-circle-o
 */
class Project extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Project;
    }
}
