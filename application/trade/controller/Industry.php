<?php

namespace app\trade\controller;

use app\common\controller\Backend;
use app\common\controller\Trade;
use fast\Tree;

/**
 * 行业管理
 *
 * @icon fa fa-industry
 */
class Industry extends Trade
{
    use \app\common\library\traits\Cascader;
    protected $categorylist = [];
    protected $noNeedRight = ['selectpage','cascader','ztreelist'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\trade\model\Industry;
    }

    public function ztreelist() {
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $this->spectacle($this->model);
        $list = $this->model
            ->field("name, id, pid as pId")
            ->where($where)
            ->order($sort, $order)
            ->select();
        $list = collection($list)->toArray();
        return json($list);
    }

}
