<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\admin\model\ModelLog;
use think\Hook;

class Log extends Backend
{
    protected $noNeedRight = ["index", 'rule', 'add', 'edit'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('ModelLog');

        $this->modelType = $this->request->param("model_type");
        $this->assignconfig("modelType", $this->modelType);

        if (!$this->request->isAjax()) {
            $path = str_replace('.', '/', strtolower($this->request->controller())) . '/' . strtolower($this->request->action());
            if ($this->modelType) {
                $path .='/model_type/'.$this->modelType;
            }
            if (!$this->auth->check($path) && !$this->auth->match($this->noNeedRight)) {
                Hook::listen('admin_nopermission', $this); $this->error(__('You have no permission'), '');
            }
        }
    }

    /**
     * 详情
     */
    public function view($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }


    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                ModelLog::setContent($params['content']);
                ModelLog::setModel($this->modelType, $params['model_id']);
                ModelLog::record($params['title'], 'manual', 'normal');
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("modelType",$this->request->param("model_type"));
        return $this->view->fetch();
    }
}
