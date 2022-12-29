<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Tree;

/**
 * 知识分类表
 *
 * @icon fa fa-circle-o
 */
class Warerange extends Backend
{
    use \app\common\library\traits\Cascader;

    /**
     * Lorerange模型对象
     * @var \app\common\model\Lorerange
     */
    protected $model = null;
    protected $categorylist = [];
    protected $noNeedRight = ['selectpage','select'];

    public $beforeActionList = [
        'setParentList' =>  ['only'=>'add,edit'],
    ];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\Warerange;
    }

    protected function setParentList() {
        $tree = Tree::instance();
        $tree->init(collection($this->model->order('id desc')->select())->toArray(), 'pid');
        $this->categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = [0 => ['type' => 'all', 'name' => __('None')]];
        foreach ($this->categorylist as $k => $v) {
            $categorydata[$v['id']] = $v;
        }
        $this->view->assign("parentList", $categorydata);
    }

    public function classtree() {
        $chequelList = [];
        $id = $this->request->param("id");
        $pids = $id ? $id : $this->request->param("pid", [0]);
        $pids = is_array($pids)?$pids:explode(",", $pids);
        $promotion = $this->request->param("promotion");

        if (!$id && $pids) {
            $where = [];
            if ($promotion == null) {
                $where['promotion'] = 0;
            }
            foreach($pids as $pid) {
                $ware = $this->model->where($where)->where("status",'neq', 'hidden')->where("id", $pid)->find();
                if ($ware) {
                    $chequelList[] =  [
                        'id'     => $ware['id'],
                        'isParent'=>true,
                        'name'   => $ware['name'],
                        'open'  => true,
                        'childOuter'=>false,
                        'status'   => $ware['status'],
                        'type'   => "warerange",
                        'model_type'=>$ware['model_type'],
                        'relation_type'=>$ware['relation_type'],
                        'surface'=>$ware['surface'],
                    ];
                }
            }
        }

        $lids = $this->request->param("lids");
        if ($lids) {
            $lids = is_array($lids)?$lids:explode(",", $lids);
        }

        foreach($pids as $pid) {
            $where = [];
            if ($promotion == null) {
                $where['promotion'] = 0;
            }
            $where['pid'] = $pid;
            if ($lids) {
                $where['id'] = ['in', $lids];
            }

            $list = $this->model->where($where)->where("status",'neq', 'hidden')->select();
            foreach ($list as $k => $v) {
                $item = [
                    'id' => $v['id'],
                    'isParent' => true,
                    'name' => $v['name'],
                    'open' => true,
                    'pid' => $id,
                    'childOuter' => false,
                    'status' => $v['status'],
                    'type'   => "warerange",
                    'model_type'=>$v['model_type'],
                    'relation_type'=>$v['relation_type'],
                ];
                if (isset($ware['surface'])) {
                    $item['surface'] = $ware['surface'];
                }
                $chequelList[] = $item;
            }

            if ($this->request->param("embody", false)) {
                $warehouses = model("warehouse")->where("pid", $id)->select();
                foreach ($warehouses as $k1 => $v2) {
                    $chequelList[] = [
                        'id' => "w_" . $v2['id'],
                        'isParent' => false,
                        'name' => $v2['name'],
                        'open' => false,
                        'childOuter' => false,
                        'type'   => "warehouse",
                    ];
                }
            }
        }
        return $chequelList;
    }

    public function select() {

        return $this->view->fetch();
    }
}
