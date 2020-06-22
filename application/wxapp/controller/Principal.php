<?php

namespace app\wxapp\controller;
use think\Log;
use think\Request;

class Principal extends Wxapp
{
    // 初始化
    public function __construct()
    {
        parent::__construct();
        $this->model = model('principal');
    }

    public function editfields() {
        $data = [
            "types"=>pickerfield('company','type'),
            "criterions"=>pickerfield('company','criterion'),
        ];
        $this->success(__('Login successful'), $data);
    }

    public function index() {
        $list = $this->model->with($this->relationSearch)->where("id","in", function($query){
            $query->table("__CLAIM__")->where("customer_model_id", 12)->field("principal_model_id");
        })->order("principalclass_model_id asc")->where(function($query){
            $substance_type = Request::instance()->param('substance_type');
            if (!$substance_type || $substance_type != "all") {
                $query->where('substance_type',  $substance_type);
            }
        })->select();

        $principallclass = model("Principalclass");
        foreach($list as $v) {
            if ($v['substance_type'] == "persion") {
                $principallclass->where("model_type", "neq", "persion");
                break;
            }
            $v->append(["substance"]);

        }
        $this->success(__('Login successful'), ["list"=>$list, "principal_class"=>$principallclass->select()]);
    }

    public function view() {
        $id =$this->request->param("id", null);
        if ($id === null)
            $this->error(__('Params error!'));

        $row = $this->model->find($id);
        if (!$row)
            $this->error(__('No Results were found'));
        $row->append(["substance","promotions","actualizes"]);
        $this->success(__('Login successful'), $row);
    }

    public function edit($ids = null) {
        $principal = $this->model->get($ids);
        if (!$principal) {
            $this->error(__('No Results were found'));
        }
        $params = $this->request->param("row/a");

        $db = $this->model->getQuery();
        $db->startTrans();
        try {
            $substance = $principal->substance->allowField(true)->save($params);
            if ($substance === false) {
                throw new \think\Exception($this->model->getError());
            }
            $db->commit();
            $this->success("成功");
        } catch (\think\exception\PDOException $e) {
            $db->rollback();
            $this->error($e->getMessage());
        }catch(\think\Exception $e) {
            $db->rollback();
            $this->error($e->getMessage());
        }
    }

    public function add() {
        $principalclass_model_id = $this->request->param("principalclass_model_id");
        if (!$principalclass_model_id) {
            $this->error(__('No Results were found'));
        }
        $principalclass = model("Principalclass")->get($principalclass_model_id);
        if (!$principalclass) {
            $this->error(__('No Results were found'));
        }

        $params = $this->request->param("row/a");
        if ($principalclass['id'] ==2) {
            $principal = model("principal")->where("name",$params['name'])->find();
            if ($principal) {
                $this->error("主体名称重复");
            }
        }

        $db = $this->model->getQuery();
        $db->startTrans();
        try {
            $principal = model("principal")->create([
                "name"=>$params['name'],
                "principalclass_model_id"=>$principalclass_model_id
            ]);
            if ($principal === false) {
                throw new \think\Exception($this->model->getError());
            }
            $substance = $principal->substance->allowField(true)->save($params);
            if ($substance === false) {
                throw new \think\Exception($this->model->getError());
            }
            $claim = model("claim")->create([
                "customer_model_id"=>12,
                "principal_model_id"=>$principal['id']

            ]);
            if ($claim !== false) {
                $db->commit();
                $this->success("主体添加成功", "/principal/index?id=".$principal->id);
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
