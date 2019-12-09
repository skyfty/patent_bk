<?php

namespace app\customer\controller;

use think\Db;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Promotion extends Customer
{
    public function __construct() {
        parent::__construct();
        $this->model = model("promotion");
    }

    public function view($id)
    {
        $row = $this->model->find($id);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    public function pay()
    {
        return $this->view->fetch();
    }

    public function staffs() {
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $list = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
        $this->result(collection($list)->toArray(), 1);
    }
}
