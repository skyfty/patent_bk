<?php

namespace app\trade\controller;

use think\Config;

/**
 * 政策管理
 *
 * @icon fa fa-circle-o
 */
class Policy extends Trade
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\trade\model\Policy;
    }

    protected function spectacle($model) {
        return $model;
    }

    public function rest($ids = NULL) {
        $row = $this->model->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $row->match();
        $this->success();

    }
}
