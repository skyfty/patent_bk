<?php

namespace app\admin\controller;

use app\admin\model\Fields;
use app\admin\model\Modelx;
use app\common\controller\Backend;
use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\admin\model\Staff;
use app\common\model\Statistics;

use think\App;
use think\Session;

/**
 * 后台控制器基类
 */
class Cosmetic extends Backend
{
    protected $modelValidate = true;
    protected $selectpageFields = ['name', 'idcode', 'id', 'status'];
    protected $selectpageShowFields = ['name', 'idcode'];
    protected $searchFields = ['name', 'idcode'];

    protected $modelSceneValidate = true;
    protected $dataLimit = false;
    protected $dataLimitFieldAutoFill = true;
    protected $dataLimitField = "owners_model_id";

    protected $relationSearch =[];
    protected $viewScenerys = ['view','block'];
    protected $noNeedRight = ["statistic",'graph', 'hinder','schedule','qrcode','summation','classtree','alltree'];

    public function _initialize()
    {
        parent::_initialize();

        $this->admin = Session::get('admin');
        if ($this->admin) {
            $this->assignconfig('admin_id', $this->admin['id']);
            $admin_branch_model_id = 0;
            if ($this->admin['staff_id'] &&($this->staff = Staff::where(['admin_id'=>$this->admin['id']])->field('group_model_id,id,admin_id,admin_name,idcode,name,branch_model_id')->find())) {
                if ($this->staff->branch_model_id != 0) {
                    $this->dataLimit = "auth";
                    $admin_branch_model_id = $this->staff->branch_model_id;
                }
                $this->assignconfig('staff', $this->staff);
            } else {
                $this->assignconfig('staff', null);
            }
            $this->assignconfig('admin_branch_model_id', $admin_branch_model_id);
            $this->assignconfig('auth_ids', $this->auth->getChildrenAdminIds(true));
        }
    }

    protected function getDataLimitAdminIds()
    {
        $filter = $this->request->get("filter", '');
        $filter = (array)json_decode($filter, TRUE);
        if (isset($filter['owners_model_id']) || isset($filter['creator_model_id'])) {
            return null;
        }
        if ($this->auth->isSuperAdmin()) {
            return null;
        }
        if ($this->dataLimit == "auth" && $this->staff) {
            return null;
        } else {
            return parent::getDataLimitAdminIds();
        }
    }

    public function _empty($name)
    {
        $cosmeticModel = Modelx::get(['table' => strtolower($this->model->raw_name)],[],!App::$debug);
        if (!$cosmeticModel) {
            $this->error('未找到对应模型');
        }
        $scenery = Scenery::get(['model_id' => $cosmeticModel->id, 'name' =>$name],[],!App::$debug);
        if ($scenery) {
            return call_user_func([$this, $scenery['pos']]);
        }
    }
    protected function getRelationModel($cosmeticModel) {
        $relationFields = [];
        $modelFields = model("fields")->where("relevance","neq","")->where(array("model_id"=>$cosmeticModel['id']))->where("type","in", ["model","cascader"] )->where("name", "not in",['group'])->cache(!App::$debug)->order("id", "ASC")->select();
        foreach($modelFields as $v) {
            $relationFields[$v['relevance']][] = $v['name'];
        }
        return $relationFields;
    }

    protected function getRelationSearch($cosmeticModel) {
        $relationFields = [];
        $modelFields = model("fields")->where(array("model_id"=>$cosmeticModel['id']))->where("type","in", ["model","cascader"] )->where("name", "not in",['group'])->cache(!App::$debug)->order("id", "ASC")->select();
        foreach($modelFields as $v) {
            if ($v['relevance']) {
                $idx =array_search($v['relevance'],$relationFields);
                if ($idx !== false) {
                    unset($relationFields[$idx]);
                }
                $relationFields[] = $v['relevance'].".".$v['name'];
            } else {
                $relationFields[] = $v['name'];
            }
        }
        return array_unique(array_merge($this->relationSearch, $relationFields));
    }

    protected function assignScenery($model_id, $pos, $sceneryWhere = array()) {
        $sceneryList = [];
        $sceneryWhere = array_merge(['model_id' => $model_id, 'pos' => array("in", $pos)],$sceneryWhere);
        foreach (Scenery::where($sceneryWhere)->cache(!App::$debug)->order("weigh", "ASC")->select() as $k=>$v) {
            $where =array(
                'scenery_id'=>$v['id']
            );
            $v['fields'] = Sight::with('fields')->cache(!App::$debug)->where($where)->order("weigh", "asc")->select();
            $sceneryList[$v['pos']][] = $v;
        }

        $allfields = [];
        foreach (Fields::where(array("model_id"=>$model_id))->cache(!App::$debug)->field('createtime,updatetime',true)->order("id", "ASC")->select() as $k=>$v) {
            $allfields[$v['id']] = $v;
        }

        $this->assignconfig('allFields', $allfields);
        $this->assignconfig('sceneryList', $sceneryList);
    }

    public function statistic() {
        $stat = Statistics::where(function($query){
            $table = $this->request->param("table");
            if (!$table) {
                $table = strtolower($this->model->raw_name);
            }
            $query->where("table", $table);

            $fields = $this->request->param("field/a");
            if ($fields) {
                $query->where("field", "in", $fields);
            }

        })->column('sum(value),field','field');

        $this->result($stat, 1);
    }

    public function index() {
        $this->request->filter(['strip_tags']);

        $cosmeticModel = Modelx::get(['table' => strtolower($this->model->raw_name)],[],!App::$debug);
        if (!$cosmeticModel) {
            $this->error('未找到对应模型');
        }
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage($this->searchFields);
            }
            $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));

            $relationSearch = $this->getRelationSearch($cosmeticModel);;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $this->model->alias($name)->where($where)->with($relationSearch);
            $this->spectacle($this->model);
            $total = $this->model->count();

            $this->model->alias($name)->where($where)->with($relationSearch)->order($sort, $order)->limit($offset, $limit);
            $this->spectacle($this->model);
            $list = $this->model->select();

            $relationModel = $this->getRelationModel($cosmeticModel);;
            foreach($list as $row) {
                foreach($relationModel as $rmk=>$rm) {
                    $row->appendRelationAttr($rmk, $rm);
                }
            }
            return json(array("total" => $total, "rows" => collection($list)->toArray()));
        }

        $this->assignScenery($cosmeticModel->id, ['index']);
        if ($this->view->engine->exists("")) {
            return $this->view->fetch();
        }
        return $this->view->fetch("index");
    }

    /**
     * 添加
     */
    public function add() {
        if ($this->request->isPost()) {
            return parent::add();
        }

        $cosmeticModel = Modelx::get(['table' => strtolower($this->model->raw_name)],[],!App::$debug);
        if (!$cosmeticModel) {
            $this->error('未找到对应模型');
        }
        $scenery = Scenery::get(['model_id' => $cosmeticModel->id, 'pos' => "view",'main'=>1],[],!App::$debug);
        $where =array(
            'scenery_id'=>$scenery['id']
        );
        $scenery['fields'] = Sight::with('fields')->where($where)->order("weigh", "asc")->cache(!App::$debug)->select();

        $this->assignconfig('scenery',$scenery);
        return $this->view->fetch();
    }

    public function view() {
        $ids =$this->request->param("ids", null);
        if ($ids === null)
            $this->error(__('Params error!'));

        $cosmeticModel = Modelx::get(['table' => $this->model->raw_name],[],!App::$debug);
        if (!$cosmeticModel) {
            $this->error('未找到对应模型');
        }

        $row = $this->model->with($this->getRelationSearch($cosmeticModel))->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        $relationModel = $this->getRelationModel($cosmeticModel);;
        foreach($relationModel as $rmk=>$rm) {
            $row->appendRelationAttr($rmk, $rm);
        }
        $this->view->assign("row", $row);

        if ($this->request->isAjax()) {
            if ($this->request->has("scenery_id")) {
                $scenery_id = $this->request->param("scenery_id");
                $fields =  Sight::with('fields')->where(['scenery_id'=>$scenery_id])->order("weigh", "asc")->cache(!App::$debug)->select();;
            }else {
                $scenery = Scenery::get(['model_table' => $this->model->raw_name,'name'=>$this->request->action(),'pos'=>'view'],[],!App::$debug);
                $where =array(
                    'scenery_id'=>$scenery['id']
                );
                $fields =  Sight::with('fields')->where($where)->order("weigh", "asc")->cache(!App::$debug)->select();;
            }
            $content = $this->view->fetch();
            return array("content"=>$content, "fields"=>$fields);
        } else {

            $this->assignScenery($cosmeticModel->id, $this->viewScenerys);
            return $this->view->fetch();
        }
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)  {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if (!$this->request->isPost()) {
            $this->error(__('An unexpected error occurred'));
        }
        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $db = $this->model->getQuery();
        $db->startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $scenery = $this->request->param("scenery");
                $scenery = $scenery?$scenery:"edit";
                $name = basename(str_replace('\\', '/', get_class($this->model)));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.'.$scenery : true) : $this->modelValidate;
                $row->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            if ($result !== false) {
                $db->commit();
                $this->result($this->model->get($ids),1);
            } else {
                $db->rollback();
                $this->error($row->getError());
            }
        } catch (\think\exception\PDOException $e) {
            $db->rollback();
            $this->error($e->getMessage());
        }catch(\think\Exception $e) {
            $db->rollback();
            $this->error($e->getMessage());
        }
    }

    public function hinder($ids) {

        $cosmeticModel = Modelx::get(['table' => strtolower($this->model->raw_name)],[],!App::$debug);
        if (!$cosmeticModel) {
            $this->error('未找到对应模型');
        }
        $row = $this->model->with($this->getRelationSearch($cosmeticModel))->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);

        $this->assignScenery($cosmeticModel->id, ['hinder']);
        return $this->view->fetch();
    }

    public function chart() {
        return $this->view->fetch();
    }

    public function graph() {
        $data=[];

        $type = $this->request->param("type", "increased");
        $scope = $this->request->get("scope", '');
        if (!$scope) {
            return [];
        }
        $scope = explode(" - ", $scope);
        $scope[0] = strtotime($scope[0]);$scope[1] = strtotime($scope[1]);

        $xAxis = [
            "type"=>"category",
            "boundaryGap"=>false,
        ];
        for($stepscope = $scope[0];$stepscope<=$scope[1];) {
            $stepend = strtotime('+1 day',$stepscope);
            $xAxis['data'][] = date('m-d',$stepscope);
            $stepscope = $stepend;
        }
        $data['xAxis'][] = $xAxis;

        $legend = [];
        $cheque = [['name'=>'新增']];
        foreach($cheque as $ck=>$cv) {
            $legend[] = $cv['name'];
            $series=[
                "type"=>'line',
                "name"=>$cv['name'],
                "data"=>[],
            ];
            for($stepscope = $scope[0]; $stepscope<=$scope[1];) {
                $stepend = strtotime('+1 day',$stepscope);
                $this->model->where("createtime", "BETWEEN", [$stepscope, $stepend]);
                switch ($type) {
                    case "increased": {
                        $series['data'][] = $this->model->count();
                        break;
                    }
                    case "sum": {
                        $field = $this->request->param("field", "amount");
                        $series['data'][] = $this->model->sum($field);
                        break;
                    }
                }
                $stepscope = $stepend;
            }
            $data['series'][] = $series;
        }
        $data['legend']['data'] = $legend;

        $this->result($data,1);
    }

    public function summation() {
        $field = $this->request->param("field");
        if (!$field)
            $this->error(__('Params error!'));
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $this->result($this->model->where($where)->with($this->relationSearch)->sum($field),1);
    }

    public function avatar($ids) {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        try {
            $result = $row->allowField(true)->save($params);
            if ($result !== false) {
                $this->result($this->model->get($ids),1);
            } else {
                $this->error($row->getError());
            }
        } catch (\think\exception\PDOException $e) {
            $this->error($e->getMessage());
        }
    }

}
