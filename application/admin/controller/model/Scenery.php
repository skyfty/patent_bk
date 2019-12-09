<?php

namespace app\admin\controller\model;

use app\admin\model\Modelx;
use app\common\controller\Backend;
use think\Db;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Scenery extends Backend
{
    
    /**
     * Scenery模型对象
     * @var \app\admin\model\Scenery
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Scenery');
        $this->view->assign("posList", $this->model->getPosList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("tableList", Modelx::all());
    }

    public function index()
    {
        $model_id = $this->request->param('model_id');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where('model_id', $model_id)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where('model_id', $model_id)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->assignconfig('model_id', $model_id);
        return $this->view->fetch();
    }

    /**
     * 通用排序
     */
    public function weigh() {
        //排序的数组
        $ids = $this->request->post("ids");
        //拖动的记录ID
        $changeid = $this->request->post("changeid");

        //排序的方式
        $orderway = 'ASC';
        $sour = $weighdata = [];
        $ids = explode(',', $ids);
        $prikey = 'id';
        //限制更新的字段
        $field = 'weigh';

        $list = Db::name("scenery")->field("$prikey,$field")->where($prikey, 'in', $ids)->order($field, $orderway)->select();
        foreach ($list as $k => $v) {
            $sour[] = $v[$prikey];
            $weighdata[$v[$prikey]] = $v[$field];
        }
        $position = array_search($changeid, $ids);
        $desc_id = $sour[$position];    //移动到目标的ID值,取出所处改变前位置的值
        $sour_id = $changeid;
        $weighids = array();
        $temp = array_values(array_diff_assoc($ids, $sour));
        foreach ($temp as $m => $n) {
            if ($n == $sour_id) {
                $offset = $desc_id;
            } else {
                if ($sour_id == $temp[0]) {
                    $offset = isset($temp[$m + 1]) ? $temp[$m + 1] : $sour_id;
                } else {
                    $offset = isset($temp[$m - 1]) ? $temp[$m - 1] : $sour_id;
                }
            }
            $weighids[$n] = $weighdata[$offset];
            Db::name("scenery")->where($prikey, $n)->update([$field => $weighdata[$offset]]);
        }
        $this->success();

    }
}
