<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Tree;

/**
 * 行业管理
 *
 * @icon fa fa-industry
 */
class Industry extends Backend
{
    use \app\admin\library\traits\Cascader;
    protected $categorylist = [];
    protected $noNeedRight = ['selectpage','cascader'];


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Industry;

        $tree = Tree::instance();
        $tree->init(collection($this->model->order('id desc')->select())->toArray(), 'pid');
        $this->categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = [0 => ['type' => 'all', 'name' => __('None')]];
        foreach ($this->categorylist as $k => $v) {
            $categorydata[$v['id']] = $v;
        }
        $this->view->assign("parentList", $categorydata);
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
