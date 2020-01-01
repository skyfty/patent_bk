<?php

namespace app\admin\controller\auth;

use app\common\controller\Backend;
use fast\Tree;
/**
 * 部门表
 *
 * @icon fa fa-circle-o
 */
class Commission extends Backend
{
    
    /**
     * AuthDepartment模型对象
     * @var \app\admin\model\Department
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Commission');

        $commissionList = collection(\app\admin\model\Commission::all())->toArray();

        Tree::instance()->init($commissionList);
        $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));

        $commissionName = array(0=>"根级");
        foreach ($result as $k => $v)
        {
            $commissionName[$v['id']] = $v['name'];
        }
        $this->commissiondata = $commissionName;
        $this->view->assign('commissiondata', $this->commissiondata);
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("rankList", $this->model->getRankList());

    }


    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            $list = \app\admin\model\Commission::all(array_keys($this->commissiondata));
            $list = collection($list)->toArray();
            $commissionList = [];
            foreach ($list as $k => $v)
            {
                $commissionList[$v['id']] = $v;
            }
            $list = [];
            foreach ($this->commissiondata as $k => $v)
            {
                if (isset($commissionList[$k]))
                {
                    $commissionList[$k]['name'] = $v;
                    $list[] = $commissionList[$k];
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
            $childrenCommissionIds = $this->auth->getChildrenCommissionIds($row['id'], $row['pid']);
            if (in_array($params['pid'], $childrenCommissionIds))
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
            $commissionlist = $this->auth->getGroups();
            $commission_ids = array_map(function($group) { return $group['id']; }, $commissionlist);
            // 移除掉当前管理员所在组别
            $ids = array_diff($ids, $commission_ids);

            $commissionlist = $this->model->where('id', 'in', $ids)->select();
            foreach ($commissionlist as $k => $v)
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
