<?php

namespace app\admin\controller;

use app\admin\model\Fields;
use app\admin\model\Modelx;
use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\common\controller\Backend;
use think\App;

/**
 * 用户主体
 *
 * @icon fa fa-circle-o
 */
class Principal extends Cosmetic
{
    
    /**
     * Principal模型对象
     * @var \app\admin\model\Principal
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Principal;
    }

    protected function mergerow(&$row) {
        $scenery = Scenery::where(["model_table"=>$row['substance_type'],"pos"=>'view'])->cache(!App::$debug)->find();
        $fields = Sight::with('fields')->cache(!App::$debug)->where(['scenery_id'=>$scenery['id']])->column("fields.name");
        foreach($row->substance->getData() as $k=>$v) {
            if (in_array($k, $fields)) {
                $row[$k] = $v;
            }
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
        $row->append(["substance"]);
        $this->view->assign("row", $row);

        $sceneryList = [];
        $sceneryWhere = array_merge(['model_id' => $cosmeticModel->id, 'pos' => array("in", "hinder")]);
        foreach (Scenery::where($sceneryWhere)->cache(!App::$debug)->order("weigh", "ASC")->select() as $k=>$v) {
            if ($v['name'] == "substance"){
                $principal_type = $row['principalclass']['model_type'];
                $principal_type_scenery = Scenery::where(["model_table"=>$principal_type,"pos"=>'view'])->cache(!App::$debug)->order("weigh", "ASC")->find();
                $where =array(
                    'scenery_id'=>$principal_type_scenery['id']
                );
            } else {
                $where =array(
                    'scenery_id'=>$v['id']
                );
            }
            $v['fields'] = Sight::with('fields')->cache(!App::$debug)->where($where)->order("weigh", "asc")->select();
            $sceneryList[$v['pos']][] = $v;
        }
        $this->assignconfig('sceneryList', $sceneryList);
        return $this->view->fetch();
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
            foreach($list as $k=>$row) {
                $scenery = Scenery::where(["model_table"=>$row['substance_type'],"pos"=>'view'])->cache(!App::$debug)->find();
                $fields = Sight::with('fields')->cache(!App::$debug)->where(['scenery_id'=>$scenery['id']])->select();
                $list[$k]['substance_fields'] = $fields;
            }
            if ($total > 0) {
                $rows = collection($list)->append(["substance"])->toArray();
            } else {
                $rows = [];
            }
            return json(array("total" => $total, "rows" =>$rows ));
        }

        $this->assignScenery($cosmeticModel->id, ['index']);
        if ($this->view->engine->exists("")) {
            return $this->view->fetch();
        }
        return $this->view->fetch("index");
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
        $row->append(["substance"]);
        $this->mergerow($row);
        $this->view->assign("row", $row);

        if ($this->request->isAjax()) {
            if ($this->request->has("scenery_id")) {
                $scenery_id = $this->request->param("scenery_id");
                $fields =  Sight::with('fields')->where(['scenery_id'=>$scenery_id])->order("weigh", "asc")->cache(!App::$debug)->select();;
            }else {

                $action = $this->request->action();
                if ($action == "substance") {
                    $principal_type = $row['principalclass']['model_type'];
                    $principal_type_scenery = Scenery::where(["model_table"=>$principal_type,"pos"=>'view'])->cache(!App::$debug)->order("weigh", "ASC")->find();
                    $where =array(
                        'scenery_id'=>$principal_type_scenery['id']
                    );
                } else {
                    $scenery = Scenery::get(['model_table' => $this->model->raw_name,'name'=>$this->request->action(),'pos'=>'view'],[],!App::$debug);
                    $where =array(
                        'scenery_id'=>$scenery['id']
                    );
                }
                $fields =  Sight::with('fields')->where($where)->order("weigh", "asc")->cache(!App::$debug)->select();;
            }
            $content = $this->view->fetch();
            return array("content"=>$content, "fields"=>$fields);
        } else {
            $sceneryList = [];
            $sceneryWhere = array_merge(['model_id' => $cosmeticModel->id, 'pos' => array("in", $this->viewScenerys)]);
            if ($row['substance_type'] == "persion") {
                $sceneryWhere['name'] = ["neq", "quarters"];
            }
            foreach (Scenery::where($sceneryWhere)->cache(!App::$debug)->order("weigh", "ASC")->select() as $k=>$v) {
                if ($v['name'] == "substance"){
                    $principal_type = $row['principalclass']['model_type'];
                    $principal_type_scenery = Scenery::where(["model_table"=>$principal_type,"pos"=>'view'])->cache(!App::$debug)->order("weigh", "ASC")->find();
                    $where =array(
                        'scenery_id'=>$principal_type_scenery['id']
                    );
                } else {
                    $where =array(
                        'scenery_id'=>$v['id']
                    );
                }
                $v['fields'] = Sight::with('fields')->cache(!App::$debug)->where($where)->order("weigh", "asc")->select();
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
        if (!$this->request->isPost()) {
            $this->error(__('An unexpected error occurred'));
        }
        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params['principalclass_model_id'] = $row['principalclass_model_id'];

        $db = $this->model->getQuery();
        $db->startTrans();
        try {

            $result = $row->allowField(true)->validate("principal.edit")->save($params);
            if ($result !== false) {
                $db->commit();
                $row = $this->model->get($ids);
                $this->mergerow($row);
                $this->result($row,1);
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

}
