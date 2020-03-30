<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 文章管理
 *
 * @icon fa fa-circle-o
 */
class Article extends Cosmetic
{
    protected $selectpageFields = ['name', 'idcode', 'id', 'content'];
    protected $noNeedRight = ['preview'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Article;
    }

    public function preview($id) {
        $row = $this->model->with($this->relationSearch)->find($id);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


    protected function spectacle($model) {
        return $model;
    }

}
