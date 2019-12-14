<?php

namespace app\admin\controller;

use app\admin\model\Modelx;
use app\common\controller\Backend;
use fast\Tree;

/**
 * 分类表
 *
 * @icon fa fa-circle-o
 */
class Chequecate extends Backend
{
    /**
     * Chequecate模型对象
     * @var \app\admin\model\Chequecate
     */
    protected $model = null;
    protected $noNeedRight = ['classtree'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Chequecate');
        $this->view->assign("typeList", Modelx::where(['accountswitch'=>1])->cache(true)->column('id,name,table'));

        $categorydata = [0 => ['type' => 'all', 'name' => __('None')]];
        $tree = Tree::instance();
        $row = $this->model->cache(true)->select();
        if ($row) {
            $coll = collection($row)->append(["cheque_text"]);
            $tree->init($coll->toArray(), 'pid');
            $this->categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
            foreach ($this->categorylist as $k => $v)
            {
                $categorydata[$v['id']] = $v;
            }
        } else {
            $this->categorylist = [];
        }
        $this->view->assign("parentList", $categorydata);
    }


    /**
     * 查看
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }

        $list = [];
        if ($this->categorylist) {
            $search = $this->request->request("search");
            $type = $this->request->request("type");
            foreach ($this->categorylist as $k => $v) {
                if ($search) {
                    if ($v['type'] == $type && stripos($v['name'], $search) !== false) {
                        if($type == "all" || $type == null) {
                            $list = $this->categorylist;
                        } else {
                            $list[] = $v;
                        }
                    }
                } else {
                    if($type == "all" || $type == null) {
                        $list = $this->categorylist;
                    } else if ($v['type'] == $type){
                        $list[] = $v;
                    }
                }
            }
        }
        $total = count($list);
        $result = array("total" => $total, "rows" => $list);
        return json($result);

    }

    public function classtree() {
        $where = array();
        $type = $this->request->param("type");
        if ($type) {
            $where['type'] = array("in", explode(",",$type));
        }
        $list = $this->model->cache(true)->where($where)->select();

        $chequelList = [];
        foreach (collection($list)->append(["cheques"])->toArray() as $k => $v) {
            $chequelList[] = [
                'id'     => "tree_".$v['id'],
                'parent' => $v['pid'] ? "tree_".$v['pid'] : '#',
                'text'   => __($v['name']),
                'type'   => "list",
                'state'  => ['opened' => false]
            ];
            if ($v['cheques']) {
                foreach($v['cheques'] as $v2) {
                    $chequelList[] = [
                        'id'     => $v2['id'],
                        'parent' => "tree_".$v['id'],
                        'text'   => __($v2['name']),
                        'type'   => "link",
                    ];
                }
            }
        }
        return $chequelList;
    }
}
