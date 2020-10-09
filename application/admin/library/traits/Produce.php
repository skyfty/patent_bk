<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;


use app\admin\model\Modelx;
use think\App;

trait Produce
{
    protected function getModelRow($ids) {
        $cosmeticModel = Modelx::get(['table' => $this->model->raw_name],[],!App::$debug);
        $row = $this->model->with($this->getRelationSearch($cosmeticModel))->find($ids);
        if ($row) {
            if ($row['extend']) {
                $row['extend'] = json_decode($row['extend'], true);
                foreach($row['extend'] as $field_name=>$v) {
                    $row[$field_name] = $v['value'];
                }
            }
            $relationModel = $this->getRelationModel($cosmeticModel);;
            foreach($relationModel as $rmk=>$rm) {
                $row->appendRelationAttr($rmk, $rm);
            }
        }
        return $row;
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
        $procedures = model("procedure")->where("relevance_model_type",strtolower($this->model->raw_name))->where($where)->select();
        foreach($procedures as $procedure) {
            $row->produceDocument($procedure);
        }
        $this->success();
    }

    public function view() {
        $ids =$this->request->param("ids", null);
        if ($ids === null)
            $this->error(__('Params error!'));

        $row = $this->getModelRow($ids);
        if ($row === null)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);
        $model_name = strtolower($this->model->raw_name);

        if ($this->request->isAjax()) {
            return parent::view();
        } else {
            $datefmt = ['date'=>'YYYY年MM月DD日','datetime'=>'YYYY年MM月DD日 HH:mm:ss','time'=>'HH:mm:ss'];
            $sceneryList = [];
            $scenerys = model("scenery")->where(['model_table' => $model_name, 'pos' => "view"])->cache(!App::$debug)->order("weigh", "ASC")->select();
            foreach ($scenerys as $k=>$v) {
                $where =array(
                    'scenery_id'=>$v['id']
                );
                $fields = model("sight")->with('fields')->cache(!App::$debug)->where($where)->order("weigh", "asc")->select();;

                $alternatings = model("alternating")->where("type", "custom")->where("procedure_model_id", "in", function($query)use($model_name){
                    $query->table("__PROCEDURE__")->where("relevance_model_type", $model_name)->field("id");
                })->select();
                foreach($alternatings as $alternating) {
                    $field = model("fields")->where("name", "name")->where("model_table", "procedure")->find();
                    $field["type"] = $alternating['field_model_id'];
                    if (array_key_exists($field['type'], $datefmt)) {
                        $field["content"] = $datefmt[$field['type']];
                    }
                    $field["title"] = $alternating['name'];
                    $field["name"] = $alternating['field_name'];
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
            $model_name = strtolower($this->model->raw_name);
            $extend = [];
            $alternatings = model("alternating")->where("type", "custom")->where("procedure_model_id", "in", function($query)use($model_name){
                $query->table("__PROCEDURE__")->where("relevance_model_type",$model_name)->field("id");
            })->select();
            foreach($alternatings as $alternating) {
                $extend[$alternating['field_name']] = ["name"=>$alternating['name'], "value"=>$params[$alternating['field_name']]];
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
}