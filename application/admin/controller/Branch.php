<?php

namespace app\admin\controller;

use app\admin\model\CalendarEvent;
use app\admin\model\Scenery;
use app\admin\model\Sight;

/**
 * 门店管理
 *
 * @icon fa fa-circle-o
 */
class Branch extends Cosmetic
{
    use \app\admin\library\traits\GroupData;
    protected $selectpageShowFields = ['name'];
    protected $selectpageFields = ['name', 'idcode', 'id', 'status'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("branch");
        $this->dataLimit = false;
    }

    public function add() {
        if (!$this->request->isPost()) {
            return parent::add();
        }

        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $db = $this->model->getQuery();
        $db->startTrans();
        try {
            $result = $this->model->validate("branch.add")->allowField(true)->save($params);
            if ($result !== false) {
                $db->commit();
                $this->success();
            } else {
                $db->rollback();
                $this->error($this->model->getError());
            }
        } catch (\think\Exception $e) {
            $db->rollback();
            $this->error($e->getMessage());
        }

    }


    public function select() {
        $rows = $this->model->select();
        if (!$rows)
            $this->error(__('No Results were found'));
        $this->view->assign("rows", $rows);
        $this->view->engine->layout('layout/select');
        return $this->view->fetch();
    }
}
