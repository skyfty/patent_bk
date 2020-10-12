<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Tree;

/**
 * 文档目录
 *
 * @icon fa fa-circle-o
 */
class Catalog extends Backend
{
    use \app\admin\library\traits\Cascader;

    protected $categorylist = [];
    protected $noNeedRight = ['selectpage','cascader'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Catalog;
        $where = [];
        $model = $this->request->param("model",$this->request->param("custom.model"));
        if (!$model) {
            $ids = $this->request->param("ids");
            if ($ids) {
                $row = $this->model->get($ids);
                $model = $row['model'];
            }
        }
        if ($model) {
            $where['model'] = $model;
        }

        $catalogs = $this->model->where($where)->select();;
        $catalogIds = [];
        foreach ($catalogs as $k => $v) {
            $catalogIds[] = $v['id'];
        }
        $catalogList = collection(\app\admin\model\Catalog::where('id', 'in', $catalogIds)->select())->toArray();

        Tree::instance()->init($catalogList);
        $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));
        $catalogName = [];
        foreach ($result as $k => $v) {
            $catalogName[$v['id']] = $v['name'];
        }

        $this->catalogdata = $catalogName;
        $this->view->assign('catalogdata', $this->catalogdata);
    }

    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax()) {

            $searchKey = $this->request->request('searchKey');
            if ($searchKey && $searchKey == "id") {
                $list = \app\admin\model\Catalog::all($this->request->request('searchValue'));
            } else {
                $list = \app\admin\model\Catalog::all(array_keys($this->catalogdata));
            }
            $list = collection($list)->toArray();
            $catalogList = [];
            foreach ($list as $k => $v) {
                $catalogList[$v['id']] = $v;
            }
            $list = [];
            foreach ($this->catalogdata as $k => $v) {
                if (isset($catalogList[$k])) {
                    $catalogList[$k]['name'] = $v;
                    $list[] = $catalogList[$k];
                }
            }
            $total = count($list);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
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
                'parent' => ($v['pid'] && $v['pid'] != $pid) ? $v['pid'] : '#',
                'text'   => $v['name'],
                'type'   => "list",
                'state'  => ['opened' => false]
            ];
        }
        return $chequelList;
    }

}
