<?php

namespace app\admin\controller;

use app\admin\model\Modelx;
use app\common\controller\Backend;
use think\App;

/**
 * 资质管理
 *
 * @icon fa fa-circle-o
 */
class Aptitude extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Aptitude;
    }


    protected function getModelRow($ids) {
        $cosmeticModel = Modelx::get(['table' => $this->model->raw_name],[],!App::$debug);
        $row = $this->model->with($this->getRelationSearch($cosmeticModel))->find($ids);
        if ($row) {
            if ($row['extend']) {
                $row['extend'] = json_decode($row['extend']);
                foreach($row['extend'] as $field=>$v) {
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
            $scenerys = model("scenery")->where(['model_table' => "aptitude", 'pos' => "view"])->cache(!App::$debug)->order("weigh", "ASC")->select();
            foreach ($scenerys as $k=>$v) {
                $where =array(
                    'scenery_id'=>$v['id']
                );
                $fields = model("sight")->with('fields')->cache(!App::$debug)->where($where)->order("weigh", "asc")->select();;

                $alternatings = model("alternating")->where("type", "custom")->where("procedure_model_id", "in", function($query){
                    $query->table("__PROCEDURE__")->where("relevance_model_type", "aptitude")->field("id");
                })->select();
                foreach($alternatings as $alternating) {
                    $field = model("fields")->where("name", "name")->where("model_table", "procedure")->find();
                    $field["type"] = $alternating['field_model_id'];
                    $field["title"] = $alternating['name'];
                    $field["name"] = $alternating['name'];
                    $fields[] = $field;
                }
                $v['fields'] =$fields;

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
            $extend = [];
            $alternatings = model("alternating")->where("type", "custom")->where("procedure_model_id", "in", function($query){
                $query->table("__PROCEDURE__")->where("relevance_model_type", "aptitude")->field("id");
            })->select();
            foreach($alternatings as $alternating) {
                $extend[$alternating['name']] = $params[$alternating['name']];
            }
            $params['extend'] = json_encode($extend, JSON_UNESCAPED_UNICODE);

            $result = $row->allowField(true)->validate("Aptitude.view")->save($params);
            if ($result !== false) {
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


    public function produce() {
        $row = $this->model->where("id",$this->request->param("id"))->find();
        if (!$row)
            $this->error(__('No Results were found'));

        $where = [];
        $procedure_ids = $this->request->param("procedure_ids/a");
        if ($procedure_ids) {
            $where['id']=["in",$procedure_ids ];
        }
        $procedures = model("procedure")->where("relevance_model_type","aptitude")->where($where)->select();
        foreach($procedures as $procedure) {
            $row->produceDocument($procedure);
        }
        $this->success();
    }

    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("aptitude.branch_model_id", $branch_model_id);

        return $model;
    }
}
