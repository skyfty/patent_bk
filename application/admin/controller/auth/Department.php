<?php

namespace app\admin\controller\auth;

use app\common\controller\Backend;
use fast\Tree;
/**
 * 部门表
 *
 * @icon fa fa-circle-o
 */
class Department extends Backend
{
    
    /**
     * AuthDepartment模型对象
     * @var \app\admin\model\Department
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Department');

        $departmentList = collection(\app\admin\model\Department::all())->toArray();

        Tree::instance()->init($departmentList);
        $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));

        $departmentName = array(0=>__("RootDepartment"));
        foreach ($result as $k => $v)
        {
            $departmentName[$v['id']] = $v['name'];
        }
        $this->departmentdata = $departmentName;
        $this->view->assign('departmentdata', $this->departmentdata);
        $this->view->assign("statusList", $this->model->getStatusList());
    }


    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            $list = \app\admin\model\Department::all(array_keys($this->departmentdata));
            $list = collection($list)->toArray();
            $departmentList = [];
            foreach ($list as $k => $v)
            {
                $departmentList[$v['id']] = $v;
            }
            $list = [];
            foreach ($this->departmentdata as $k => $v)
            {
                if (isset($departmentList[$k]))
                {
                    $departmentList[$k]['name'] = $v;
                    $list[] = $departmentList[$k];
                }
            }
            $total = count($list);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a", [], 'strip_tags');
            // 父节点不能是它自身的子节点
            $childrenDepartmentIds = $this->auth->getChildrenDepartmentIds($row['id'], $row['pid']);
            if (in_array($params['pid'], $childrenDepartmentIds))
            {
                $this->error(__('The parent group can not be its own child'));
            }
            return parent::edit($ids);
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids)
        {
            $ids = explode(',', $ids);
            $departmentlist = $this->auth->getGroups();
            $department_ids = array_map(function($group) {
                return $group['id'];
            }, $departmentlist);
            // 移除掉当前管理员所在组别
            $ids = array_diff($ids, $department_ids);

            $departmentlist = $this->model->where('id', 'in', $ids)->select();
            foreach ($departmentlist as $k => $v)
            {
                $groupone = $this->model->get(['pid' => $v['id']]);
                if ($groupone)
                {
                    $ids = array_diff($ids, [$v['id']]);
                    continue;
                }
            }
            if (!$ids)
            {
                $this->error(__('You can not delete group that contain child group and administrators'));
            }
            $count = $this->model->where('id', 'in', $ids)->delete();
            if ($count)
            {
                $this->success();
            }
        }
        $this->error();
    }
}
