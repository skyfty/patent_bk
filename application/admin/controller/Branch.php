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
    protected $selectpageFields = ['name', 'idcode', 'id', 'status','mailing_model_id'];

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
                model("AuthGroup")->get(2)->migrate($this->model->id);
                \app\common\library\Aip::groupAdd("customer_branch_".$this->model->id);

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

    public function account($ids = null) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);

        $scenery = Scenery::get(['model_table' => 'account', 'name'=>"branch"],[],true);
        $where =array(
            'scenery_id'=>$scenery['id'],
            "fields.name"=>array("not in", array("weigh","branch",'reckon','type'))
        );
        $fields =  Sight::with('fields')->where($where)->order("weigh", "DESC")->cache(true)->select();;
        $content = $this->view->fetch();
        return array("content"=>$content, "fields"=>$fields);
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
