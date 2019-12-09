<?php

namespace app\admin\controller;

use app\admin\model\Fields;
use app\admin\model\Modelx;
use app\common\controller\Backend;
use app\admin\model\ModelGroup;
use think\Hook;
use think\Session;

/**
 * 分组管理
 *
 * @icon fa fa-circle-o
 */
class Group extends Backend
{
    protected $modelValidate = true;
    protected $modelSceneValidate = true;
    protected $noNeedRight = ["index", 'rule','uninfix','infix','select','rerule'];
    protected $selectpageFields = ['title', 'id','status','type','branch_model_id'];
    protected $selectpageShowFields = ['title'];

    public function _initialize()
    {
        parent::_initialize();

        $this->admin = Session::get('admin');
        if ($this->admin && $this->admin['staff_id'] &&($this->staff = model("staff")->get(['admin_id'=>$this->admin['id']]))) {
            $this->assignconfig('staff', $this->staff);
        } else {
            $this->assignconfig('staff', null);
        }

        $this->model = model('ModelGroup');
        $this->modelType = $this->request->param("model_type");
        $this->assignconfig("modelType", $this->modelType);
        $this->view->assign("typeList", ModelGroup::getTypeList());
    }

    public function rule($ids) {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost()) {
            $content = $this->request->post("content/a");
            if ($content) {
                try {
                    $result = $row->save(array("content"=>$content));
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->assignconfig("row", $row);

        if ($row['type'] == "cond") {
            $fields = Modelx::get(['table' => $row['model_type']],[],true)->fields()->select();
            $this->assignconfig("fields", $fields);
        }
        return $this->view->fetch($row['type']);
    }

    public function rerule() {
        $group_id = $this->request->param("group_id");
        $row = $this->model->get($group_id);
        if (!$row)
            $this->error(__('No Results were found'));

        $ids = $this->request->param("ids");
        if (is_string($ids)) $ids = explode(",", $ids);
        $row['content'] = array_diff($row['content'], $ids);
        try {
            $result = $row->save();
            if ($result !== false) {
                $this->success("", null, $ids);
            } else {
                $this->error($row->getError());
            }
        } catch (\think\exception\PDOException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Selectpage的实现方法
     *
     * 当前方法只是一个比较通用的搜索匹配,请按需重载此方法来编写自己的搜索逻辑,$where按自己的需求写即可
     * 这里示例了所有的参数，所以比较复杂，实现上自己实现只需简单的几行即可
     *
     */
    protected function selectpage($searchfields = null)
    {
        return parent::selectpage($searchfields);
    }

    /**
     * 选择附件
     */
    public function select()
    {
        return $this->view->fetch();
    }


    public function infix()
    {
        $model_type = $this->request->param("model_type");
        $model_id = $this->request->param("model_id");
        if (!$model_type || !$model_id)
            $this->error(__('Params erros'));
        $row = model($model_type)->get($model_id);
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost()) {
            $group = $this->model->get($this->request->param("group_model_id"));
            if (!$group)
                $this->error(__('No Results were found'));
            $rules = $group->content;
            $rules[] = $model_id;
            $group->content = array_unique($rules);
            $result = $group->save();
            if ($result !== false) {
                $this->success();
            } else {
                $this->error($group->getError());
            }
        } else {
            $this->view->assign("row",$row );
            return $this->view->fetch();
        }
    }

    public function uninfix()
    {
        $model_type = $this->request->param("model_type");
        $model_id = $this->request->param("model_id");
        $ids = $this->request->param("ids");
        if (!$model_type || !$model_id || !$ids)
            $this->error(__('Params erros'));

        $group = $this->model->get($ids);
        if (!$group)
            $this->error(__('No Results were found'));
        $rules = $group->content;
        $key = array_search($model_id,$rules);
        if ($key !== false) {
            array_splice($rules, $key, 1);
        }
        $group->content = array_unique($rules);
        $result = $group->save();
        if ($result !== false) {
            $this->success();
        } else {
            $this->error($group->getError());
        }
    }

    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if (!$branch_model_id) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("branch_model_id", $branch_model_id);

        return $model;
    }
}
