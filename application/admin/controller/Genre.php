<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Tree;

/**
 * 课程分类表
 *
 * @icon fa fa-circle-o
 */
class Genre extends Backend
{
    use \app\common\library\traits\Cascader;

    
    /**
     * Genre模型对象
     * @var \app\common\model\Genre
     */
    protected $model = null;
    protected $categorylist = [];
    protected $noNeedRight = ['selectpage','cascader'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Genre;

        $tree = Tree::instance();
        $tree->init(collection($this->model->order('id desc')->cache(true)->select())->toArray(), 'pid');
        $this->categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = [0 => ['type' => 'all', 'name' => __('None')]];
        foreach ($this->categorylist as $k => $v) {
            $categorydata[$v['id']] = $v;
        }
        $this->view->assign("parentList", $categorydata);
    }

    public function classtree() {
        $where = array();
        $type = $this->request->param("type");
        if ($type) {
            $where['type'] = array("in", explode(",",$type));
        }
        $pid = $this->request->param("pid");
        if ($pid) {
            $where['pid'] = $pid;
        }
        $list =collection($this->model->where($where)->cache(true)->select())->toArray() ;

        $chequelList = [];
        foreach ($list as $k => $v) {
            $chequelList[] = [
                'id'     => $v['id'],
                'parent' => ($v['pid'] && $v['pid'] != $pid) ? $v['pid'] : '#',
                'text'   => $v['name'],
                'type'   => "list",
                'state'  => ['opened' => false]
            ];
        }
        return $chequelList;
    }
}
