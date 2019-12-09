<?php

namespace app\admin\controller\model;

use app\common\controller\Backend;

/**
 * 视图字段
 *
 * @icon fa fa-circle-o
 */
class Sight extends Backend
{
    
    /**
     * Sight模型对象
     * @var \app\admin\model\Sight
     */
    protected $model = null;


    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Sight');
    }

    /**
     * 查看
     */
    public function index()
    {
        $scenery_id = $this->request->param('scenery_id');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with('fields,scenery')
                ->where('scenery_id', $scenery_id)
                ->where($where)
                ->count();

            $list = $this->model
                ->with(['fields'=>['title'],'scenery'=>['title']])
                ->where('scenery_id', $scenery_id)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        $this->assignconfig('scenery', model('scenery')->get($scenery_id));
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a", [], 'strip_tags');
            $fields_ids = $this->model->where("scenery_id", $params['scenery_id'])->column('fields_id');
            $fields_id = explode(",", $params['fields_id']);
            unset($params['fields_id']);
            foreach($fields_id as $v) {
                if (in_array($v, $fields_ids))
                    continue;
                $params['fields_id'] = $v;
                $this->model->create($params);
            }
            $this->success();
        }
        return $this->view->fetch();
    }
}
