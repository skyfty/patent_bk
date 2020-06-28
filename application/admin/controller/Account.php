<?php

namespace app\admin\controller;

use app\admin\model\Fields;
use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\admin\model\Statistics;
use think\Db;
use think\Hook;
use app\admin\model\Cheque;

/**
 * 财务
 *
 * @icon fa fa-circle-o
 */
class Account extends Cosmetic
{
    protected $modelValidate = false;

    public $beforeActionList = [
        'setRelationSearch' =>  ['only'=>'customer,staff,branch'],
        'readinessView' =>  ['only'=>'view,hinder'],
    ];
    protected $relationSearch = ['cheque'];
    protected $dataLimitField = "creator_model_id";
    protected $viewScenerys = ['view'];
    protected $multiFields = ['weshow'];

    public function _initialize() {
        $this->noNeedRight = array_merge($this->noNeedRight, ["summation","chart",'deposit']);
        parent::_initialize();
        $this->model = model("account");
    }

    public function add() {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $params["status"] = 'locked';
            $db     = $this->model->getQuery();
            $db->startTrans();
            try {
                $result = $this->model->allowField(true)->validate("account.add")->add($params);
                if ($result !== false) {
                    $db->commit();
                    $this->success();
                } else {
                    $this->error($this->model->getError());
                }
            } catch (\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        } else {
            $scenery = Scenery::get(['model_table' => "account", 'name'=>"add",'pos' => "view"]);
            $where =array(
                'scenery_id'=>$scenery['id'],
                "fields.name"=>array("not in", array("weigh"))
            );
            $scenery['fields'] = Sight::with('fields')->where($where)->cache(true)->order("weigh", "asc")->select();
            $this->assignconfig('scenery',$scenery);
            return $this->view->fetch("add");
        }
    }

    public function deposit() {
        if (!$this->auth->check("account/add")) {
            Hook::listen('admin_nopermission', $this);
            $this->error(__('You have no permission'), '');
        }

        $sceneryName = $this->request->param("scenery", "deposit");
        $scenery = Scenery::get(['model_table' => "account", 'name'=>$sceneryName,'pos' => "view"],[],true);
        $where =array(
            'scenery_id'=>$scenery['id'],
            "fields.name"=>array("not in", array("weigh"))
        );
        $scenery['fields'] = Sight::with('fields')->where($where)->cache(true)->order("weigh", "asc")->select();
        $this->assignconfig('scenery',$scenery);
        return $this->view->fetch("add");
    }


    public function withdraw() {
        if (!$this->auth->check("account/add")) {
            Hook::listen('admin_nopermission', $this);
            $this->error(__('You have no permission'), '');
        }

        if ($this->request->isPost()) {
            $model = model("payconfirm");
            $params = $this->request->post("row/a");
            $params["status"] = 'incomplete';
            $params["reckon_type"] = 'payconfirm';
            $db     = $model->getQuery();
            $db->startTrans();
            try {
                $result = $model->allowField(true)->validate("account.add")->add($params);
                if ($result !== false) {
                    $db->commit();
                    $this->success();
                } else {
                    $this->error($model->getError());
                }
            } catch (\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        } else {
            $sceneryName = $this->request->param("scenery", "withdraw");
            $scenery = Scenery::get(['model_table' => "account", 'name' => $sceneryName, 'pos' => "view"],[],true);
            $where = array(
                'scenery_id' => $scenery['id'],
                "fields.name" => array("not in", array("weigh"))
            );
            $scenery['fields'] = Sight::with('fields')->where($where)->cache(true)->order("weigh", "asc")->select();
            $this->assignconfig('scenery', $scenery);
            return $this->view->fetch("withdraw");
        }
    }

    public function confirm($ids = null) {
        if (!$ids) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        $accounts = model("payconfirm")->where("id",'in', $ids)->where("status",'incomplete')->select();
        foreach($accounts as $ac) {
            Db::startTrans();
            try {
                $ac->confirm();
                Db::commit();
            } catch (\think\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
        $this->success();
    }

    public function settle($ids,$staff_model_id,$branch_model_id) {
        $where = [
            "account.id"=>['in', $ids],
            "account.cheque_model_id"=>34,
            "course.emolument_state"=>'1',
            "account.reckon_model_id"=>$staff_model_id
        ];
        $total = model("account")->with("course")->where($where)->sum("account.money");

        Db::startTrans();
        try {
            $result = \app\admin\model\Account::instance()->add([
                "money"=>$total,
                "cheque_model_id"=>36,
                "reckon_type"=>"staff",
                "reckon_model_id"=>$staff_model_id,
                "inflow_type"=>"branch",
                "inflow_model_id"=>$branch_model_id,
                "status"=>"locked",
            ]);
            if ($result) {
                model("account")->where("id", "in", $ids)->setField(['status'=>"locked"]);
                Db::commit();
            }
        } catch (\think\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success();
    }

    protected function assignScenery($model_id, $pos, $sceneryWhere = array()) {
        if (in_array("index",$pos)) {
            $act = strtolower($this->request->action());
            $sceneryWhere['name']= ['in', [$act, 'chart']];
        } elseif(!in_array("hinder",$pos)){
            $sceneryWhere['name'] = "view";

            $scenery = $this->request->param("scenery");
            if ($scenery) {
                $scen = Scenery::get(['model_table' => "account", 'name' => $scenery."view"],[],true);
                if ($scen) {
                    $sceneryWhere['name']=strtolower($scenery."view");
                }
            }
        }
        parent::assignScenery($model_id, $pos, $sceneryWhere);
    }

    public function setRelationSearch() {
        if ($this->request->isAjax()) {
            $this->readinessView();
        }
    }

    public function readinessView() {
        $act = strtolower($this->request->action());
        $scen = Scenery::get(['model_table' => "account", 'name' => $act],[],true);
        $where = [
            'scenery_id' => $scen['id'],
            'fields.relevance' => ['neq', '']
        ];
        $relevance = model("sight")->with("fields")->where($where)->group('fields.relevance')->cache(true)->column('fields.relevance');
        $relationSearch = array_merge($this->relationSearch, $relevance);
        if (!in_array($act, ['view','hinder']))
            $relationSearch[] = $act;
        $this->relationSearch = array_unique($relationSearch);
    }

    public function statistic() {
        $stat = [];
        foreach(Statistics::all(['table'=>'account']) as $k=>$v) {
            $stat['cheque'][$v['field']] = $v['value'];
        }
        $this->result($stat, 1);
    }

    public function graph() {
        $action = $this->request->param("action");
        if (!$action)
            $this->error(__('Params error!'));

        $scope = $this->request->get("scope", '');
        if (!$scope) {
            return [];
        }
        $scope = explode(" - ", $scope);
        $scope[0] = strtotime($scope[0]);$scope[1] = strtotime($scope[1]);

        $data=[];
        $type = $this->request->param("type", "increased");

        $legend = [];
        switch ($type) {
            case "increased":
            case "amount": {
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

                $cheque = model("cheque")->all(['reckon_table' => $action]);
                foreach ($cheque as $ck => $cv) {
                    $legend[] = $cv['name'];
                    $series = [
                        "type" => 'line',
                        "name" => $cv['name'],
                        "data" => [],
                    ];
                    for ($stepscope = $scope[0]; $stepscope <= $scope[1];) {
                        $stepend = strtotime('+1 day', $stepscope);
                        $this->model->where("createtime", "BETWEEN", [$stepscope, $stepend])->where("cheque_model_id", $cv['id']);
                        switch($type) {
                            case "increased": {
                                $series['data'][] = $this->model->count();
                                break;
                            }
                            case "amount": {
                                $series['data'][] = $this->model->sum("money");
                                break;
                            }
                        }
                        $stepscope = $stepend;
                    }
                    $data['series'][] = $series;
                }
                break;
            }
            case "classamount": {
                $cheque = model("cheque")->all(['reckon_table' => $action]);
                $series = [
                    "type" => 'pie',
                    "name" => "账目类别",
                    "radius" => "55%",
                    "center" => ['50%', '60%'],
                    "data" => [],
                ];
                foreach ($cheque as $ck => $cv) {
                    $legend[] = $cv['name'];

                    $amout = $this->model->where("createtime", "BETWEEN", [$scope[0], $scope[1]])->where("cheque_model_id", $cv['id'])->sum("money");
                    $series['data'][] = ["value"=>$amout, "name"=>$cv['name']];
                }
                $data['series'][] = $series;
                break;
            }
        }
        $data['legend']['data'] = $legend;
        $this->result($data,1);
    }

    public function chart() {
        $action = $this->request->param("action");
        if (!$action)
            $this->error(__('Params error!'));

        $content = $this->view->fetch("./account/chart/".$action);
        return array("content"=>$content);
    }

    public function summation() {
        $field = $this->request->param("field");
        if (!$field)
            $this->error(__('Params error!'));

        $scenery = $this->request->param("scenery");
        $scen = Scenery::get(['model_table' => "account", 'name' => $scenery],[],true);
        $where = [
            'scenery_id' => $scen['id'],
            'fields.relevance' => ['neq', '']
        ];
        $relevance = model("sight")->with("fields")->cache(true)->where($where)->group('fields.relevance')->column('fields.relevance');
        $relationSearch = array_unique(array_merge($this->relationSearch, $relevance,["cheque",in_array($scenery,['flow'])?"":$scenery]));

        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $opt = $field . "* cheque.mold";
        $this->result($this->model->where($where)->with($relationSearch)->sum($opt),1);
    }

    protected function spectacle($model) {
        if ($this->auth->isSuperAdmin() || $this->dataLimit != "auth" || !$this->staff) {
            return $model;
        }
        $reckon_type = $this->request->param("custom.reckon_type");
        switch($reckon_type) {
            case "branch": {
                $branch_model_id = $this->request->param("custom.".$reckon_type."reckon_model_id", $this->staff->branch_model_id);
                $model->where("reckon_model_id", $branch_model_id);
                break;
            }
            case "payconfirm": {
                break;
            }
            case "flow": {
                break;
            }
            default: {
                $branch_model_id = $this->request->param("custom.".$reckon_type.".branch_model_id", $this->staff->branch_model_id);
                $model->where($reckon_type.".branch_model_id", $branch_model_id);
                break;
            }
        }

        return $model;
    }


    protected function getRelationSearch($cosmeticModel) {
        $relationFields = [];
//        $modelFields = model("fields")->where(array("model_id"=>$cosmeticModel['id']))->where("type","in", ["model","cascader","mztree"] )->where("name", "not in",['group'])->cache(!App::$debug)->order("id", "ASC")->select();
//        foreach($modelFields as $v) {
//            if ($v['relevance']) {
//                $idx =array_search($v['relevance'],$relationFields);
//                if ($idx !== false && is_string($relationFields[$idx])) {
//                    unset($relationFields[$idx]);
//                }
//                $relationFields[$v['relevance']][] =  $v['name'];
//            } else {
//                $idx =array_search($v['name'],$relationFields);
//                if ($idx === false) {
//                    $relationFields[] = $v['name'];
//                }
//            }
//        }
        return $relationFields;
    }
}
