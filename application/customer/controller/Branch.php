<?php

namespace app\customer\controller;

use think\Db;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Branch extends Customer
{
    public function __construct() {
        parent::__construct();
        $this->model = model("branch");
    }

    public function view()
    {
        if (!$this->request->has("ids"))
            $this->error(__('Params error!'));
        $ids = $this->request->param("ids");

        $row = $this->model->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


    public function index()
    {
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $branch = $this->model->where($where)->order($sort, $order);
        if ($this->request->isAjax()) {
            $list = $branch->limit($offset, $limit)->select();
            $this->result(collection($list)->toArray(), 1);
        } else {
            $list = $branch->paginate(5,true);
            $this->assign('list', $list);
            $this->assign('page', $list->render());
        }
        return $this->view->fetch();
    }

}
