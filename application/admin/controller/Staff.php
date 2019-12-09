<?php

namespace app\admin\controller;

use app\admin\model\CalendarEvent;
use app\admin\model\Scenery;
use app\admin\model\Sight;
use fast\Tree;
use fast\Random;

/**
 * 员工管理
 *
 * @icon fa fa-circle-o
 */
class Staff extends Cosmetic
{
    use \app\admin\library\traits\GroupData;

    protected $selectpageFields = ['name', 'idcode', 'slug', 'id', 'status'];
    protected $searchFields = ['name', 'idcode', 'slug', 'id'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("staff");
        if (!$this->request->isAjax()) {
            $this->assignGroupData();
        }
    }
    public function changepass($ids = null) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if (!$params) $this->error();
            if ($params['password'])
            {
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
            }
            else
            {
                unset($params['password'], $params['salt']);
            }
            $result = model("admin")->get($row->admin_id)->save($params);
            if ($result === false) {
                $this->error($row->getError());
            }
            else{
                $this->success();
            }

        } else {
            $this->view->assign("row", $row);
            return $this->view->fetch();
        }
    }

    public function according($ids = null) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);

        $eventList = CalendarEvent::where('admin_id', $row->admin_id)->order('id desc')->select();
        $this->view->assign("eventList", $eventList);
        $content = $this->view->fetch();
        return array("content"=>$content);
    }

    public function account($ids = null) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);

        $scenery = Scenery::get(['model_table' => 'account', 'name'=>"staff"],[],true);
        $where =array(
            'scenery_id'=>$scenery['id'],
            "fields.name"=>array("not in", array("weigh",'reckon','reckon_type','type'))
        );
        $fields =  Sight::with('fields')->where($where)->order("weigh", "asc")->cache(true)->select();;
        $content = $this->view->fetch();
        return array("content"=>$content, "fields"=>$fields);
    }

    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;
        $model->where("staff.branch_model_id", $branch_model_id);

        return $model;
    }
}
