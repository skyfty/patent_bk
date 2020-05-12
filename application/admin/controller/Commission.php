<?php

namespace app\admin\controller;

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
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids)
        {
            $ids = explode(',', $ids);
            $commissionlist = $this->auth->getGroups();
            $commission_ids = array_map(function($group) { return $group['id']; }, $commissionlist);

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


    public function ztreelist() {
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $this->spectacle($this->model);
        $list = $this->model
            ->field("name, id, pid as pId")
            ->where($where)
            ->order($sort, $order)
            ->select();
        $list = collection($list)->toArray();
        return json($list);
    }

    public function classtree() {
        $where = array();
        $pid = $this->request->param("pid");
        if ($pid) {
            $where['pid'] = $pid;
        }
        $list =collection($this->model->where($where)->cache(true)->select())->toArray() ;

        $chequelList = [];
        foreach ($list as $k => $v) {
            $chequelList[] = [
                'id'     => $v['id'],
                'pid' => ($v['pid'] && $v['pid'] != $pid) ? $v['pid'] : '#',
                'text'   => $v['name'],
                'type'   => "list",
                'state'  => ['opened' => false]
            ];
        }
        return $chequelList;
    }
}
