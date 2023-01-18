<?php

namespace app\trade\controller;

use app\admin\model\Scenery;
use app\admin\model\Sight;
use think\Config;

/**
 * 政策管理
 *
 * @icon fa fa-circle-o
 */
class Promotion extends Trade
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\trade\model\Promotion;
    }


    /**
     * 查看
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            $this->assignFields("promotion",'index');
        }
        return parent::index();
    }

    protected function spectacle($model) {
        return $model;
    }
}
