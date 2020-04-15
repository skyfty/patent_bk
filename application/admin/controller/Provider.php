<?php

namespace app\admin\controller;

use app\admin\model\Course;
use app\admin\model\Fields;
use app\admin\model\Modelx;
use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\admin\model\Statistics;
use app\admin\model\TException;
use EasyWeChat\Foundation\Application;
use think\App;
use think\Config;
use think\Exception;
use think\Hook;
use think\Db;
use think\Loader;
use Endroid\QrCode\QrCode;

/**
 * 服务订单
 *
 * @icon fa fa-circle-o
 */
class Provider extends Cosmetic
{
    protected $model = null;
    protected $selectpageFields = [
        'name', 'idcode', 'id', 'state',
        'branch_model_id',
        'customer_model_id',
    ];
    protected $selectpageShowFields = ['idcode'];

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, []);
        parent::_initialize();
        $this->model = model("provider");
    }


    public function add() {
        if (!$this->request->isPost()) {
            $this->assignconfig('provider', Config::get("provider"));
            return parent::add();
        }
        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $db = $this->model->getQuery();
        $db->startTrans();
        try {
            if ($params["promotion_model_id"]) {
                unset($params["species_cascader_id"]);
                $result = $this->model->validate("provider.add")->allowField(true)->save($params);
            } else {
                $species = model("species")->get($params["species_cascader_id"]);
                $result = model($species['model'])->create([
                    "branch_model_id"=>$params["branch_model_id"],
                ]);
            }

            if ($result !== false) {
                $db->commit();
                Hook::listen('newprovider',$this->model);
                $this->success("", null, $this->model->visible([],true)->toArray());
            } else {
                $this->error($this->model->getError());
            }
        } catch (\think\Exception $e) {
            $db->rollback();
            $this->error($e->getMessage());
        }
    }

    protected function getModelRow($ids) {
        $cosmeticModel = Modelx::get(['table' => $this->model->raw_name],[],!App::$debug);
        $row = $this->model->with($this->getRelationSearch($cosmeticModel))->find($ids);
        if ($row) {
            $relevance = $row->promotion->relevance;;
            $alternat_fields = model("fields")->where("alternating",1)->where("model_table", $relevance->raw_name)->where("relevance","")->cache(!App::$debug)->select();
            foreach($alternat_fields as $field) {
                $row[$field['name']] = $relevance[$field['name']];
            }
            $relevance = $row->promotion->relevance;
            if ($relevance['extend']) {
                $relevance['extend'] = json_decode($relevance['extend']);
                foreach($relevance['extend'] as $field=>$v) {
                    $row[$field] = $v;
                }
            }

            $relationModel = $this->getRelationModel($cosmeticModel);;
            foreach($relationModel as $rmk=>$rm) {
                $row->appendRelationAttr($rmk, $rm);
            }
        }
        return $row;
    }

    public function view() {
        $ids =$this->request->param("ids", null);
        if ($ids === null)
            $this->error(__('Params error!'));

        $row = $this->getModelRow($ids);
        if ($row === null)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);


        if ($this->request->isAjax()) {
            return parent::view();
        } else {
            $sceneryList = [];
            $scenerys = model("scenery")->where(['model_table' => "provider", 'pos' => "view"])->cache(!App::$debug)->order("weigh", "ASC")->select();
            foreach ($scenerys as $k=>$v) {
                if ($v['name'] == "procedure") {
                    $fields = [];
                    foreach($row->promotion->procedure->alternatings as $alternating) {
                        if ($alternating['type'] == "custom") {
                            $field = model("fields")->where("name", "name")->where("model_table", "procedure")->find();
                            $field["type"] = $alternating['field_model_id'];
                            $field["title"] = $alternating['name'];
                            $field["name"] = $alternating['name'];
                            $fields[] = $field;
                        } else {
                            $fields[] = $alternating->field;;
                        }
                    }
                    $v['fields'] = $fields;
                } else {
                    $where =array(
                        'scenery_id'=>$v['id']
                    );
                    $v['fields'] = model("sight")->with('fields')->cache(!App::$debug)->where($where)->order("weigh", "asc")->select();
                }
                $sceneryList[$v['pos']][] = $v;
            }
            $this->assignconfig('sceneryList', $sceneryList);

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

        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $db = $this->model->getQuery();
        $db->startTrans();
        try {
            $scenery = $this->request->param("scenery");

            $result = $row->allowField(true)->validate("Provider.view")->save($params);
            if ($result !== false) {
                if ($scenery == "procedure"){
                    unset($params['id']);
                    $extend = [];
                    $alternatings = $row->promotion->procedure->alternatings()->where("type", "custom")->select();
                    foreach($alternatings as $alternating) {
                        $extend[$alternating['name']] = $params[$alternating['name']];
                        unset($params[$alternating['name']]);
                    }
                    $params['extend'] = json_encode($extend, JSON_UNESCAPED_UNICODE);
                    $row->promotion->relevance->save($params);
                }
                $db->commit();
                $this->result($this->getModelRow($ids),1);
            } else {
                $db->rollback();
                $this->error($row->getError());
            }
        } catch(\think\Exception $e) {
            $db->rollback();
            $this->error($e->getMessage());
        }
    }


    public function procedure() {
        $ids =$this->request->param("ids", null);
        if ($ids === null)
            $this->error(__('Params error!'));

        $row = $this->getModelRow($ids);
        if ($row === null)
            $this->error(__('No Results were found'));

        $this->view->assign("row", $row);

        $procedure = $row->promotion->procedure;

        $fields = [];
        foreach($procedure->alternatings as $alternating) {
            $fields[] = $alternating->field;
        }
        $content = $this->view->fetch("procedure");
        return array("content"=>$content, "fields"=>$fields, "row"=>$row);
    }
    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if (!$branch_model_id) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("provider.branch_model_id", $branch_model_id);

        return $model;
    }
}
