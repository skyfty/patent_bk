<?php

namespace app\admin\controller\model;

use app\admin\model\Modelx;
use app\common\controller\Backend;
use app\common\model\Config;

/**
 * 模型字段表
 *
 * @icon fa fa-circle-o
 */
class Fields extends Backend
{

    /**
     * Fields模型对象
     */
    protected $model = null;
    protected $modelValidate = true;
    protected $modelSceneValidate = true;

    protected $noNeedRight = ['rulelist'];

    protected $beforeActionList = [
        'formatPostParam' =>  ['only'=>'add,edit'],
    ];
    protected $searchFields = [
        'name','title'
    ];
    protected $selectpageShowFields = ['name','title'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Fields');
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign('typeList', Config::getTypeList());
        $this->view->assign('regexList', Config::getRegexList());
    }

    /**
     * 查看
     */
    public function index() {
        $model_id = $this->request->param('model_id');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
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

        $this->assignconfig('modelx', Modelx::get($model_id));
        return $this->view->fetch();
    }


    /**
     * 规则列表
     * @internal
     */
    public function rulelist() {
        //主键值
        $primaryvalue = $this->request->request("keyValue");
        if ($primaryvalue !== null) {
            $primaryvalue = explode(",", $primaryvalue);
        }

        $regexList = Config::getRegexList();
        $list = [];
        foreach ($regexList as $k => $v) {
            if ($primaryvalue !== null) {
                if (in_array($k, $primaryvalue)) {
                    $list[] = ['id' => $k, 'name' => $v];
                }
            } else {
                $list[] = ['id' => $k, 'name' => $v];
            }
        }
        return json(['list' => $list]);
    }

    public function formatPostParam() {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $this->request->post(array("row"=>$params));
        }
    }

    /**
     * 添加
     */
    public function add() {
        if (!$this->request->isPost()) {
            $model_id = $this->request->param('model_id');
            $scenery = \app\admin\model\Scenery::get(array("model_id"=>$model_id, "pos"=>"view", "main"=>1));
            if ($scenery) {
                $this->view->assign('scenery', $scenery);
            }
            return parent::add();
        }

        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $db = $this->model->getQuery();
        $db->startTrans();

        try {
            $result = $this->model->validate("fields.add")->allowField(true)->save($params);
            if ($result !== false) {
                $db->commit();
                $this->success("", null, $this->model->toArray());
            } else {
                $this->error($this->model->getError());
            }
        }catch (\think\Exception $e) {
            $db->rollback();
            $this->error($e->getMessage());
        }

    }
}
