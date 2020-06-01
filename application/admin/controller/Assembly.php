<?php

namespace app\admin\controller;


/**
 * 模板组件
 *
 * @icon fa fa-circle-o
 */
class Assembly extends Cosmetic
{
    protected $model = null;
    protected $relationSearch = ["warehouse"];
    use \app\admin\library\traits\Condition;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Assembly;
        $this->dataLimit = false;

    }

    public function classtree() {
        $warehouse_model_id = $this->request->param("warehouse_model_id");
        $warehouse = model("warehouse")->get($warehouse_model_id);

        $root =  [
            'id'     =>  "w_".$warehouse['id'],
            'isParent'=>true,
            'name'   =>$warehouse['name'],
            'open'  => true,
            'wareType'=>"warerange",
            'wareId'=>0,
            'childOuter'=>false,
            'children'=>[],
            'type'=>'warehouse',
            'model_type'=>$warehouse['model_type'],
        ];
        if ($warehouse['model_type']) {
            $root['model'] = $warehouse->model;
        }
        $this->model->where("warehouse_model_id",$warehouse_model_id);
        $assembly_model_id = $this->request->param("assembly_model_id");
        if ($assembly_model_id) {
            $this->model->where("id",$assembly_model_id);
        }

        $bodyid = null;
        $list = $this->model->select();
        foreach ($list as $k => $v) {
            $item =  [
                'id'     => "a_".$v['id'],
                'isParent'=>true,
                'name'   => $v['name'],
                'open'  => true,
                'children'=>[],
                'pid'=>$root['id'],
                'type'=>'assembly',
                'body'   => $v['body'],
            ];
            if ($v['body']) {
                $bodyid = $item['id'];
            }
            $behaviors = model("behavior")->where("assembly_model_id",$v['id'])->select();
            foreach ($behaviors as $k2 => $v2) {
                $item['children'][] =  [
                    'id'     => $v2['id'],
                    'isParent'=>false,
                    'name'   => $v2->name,
                    'open'  => true,
                    'pid'=>$item['id'],
                    'type'=>'behavior'
                ];
            }
            $root['children'][] = $item;
        }
        if ($bodyid) {
            $root['bodyid'] = $bodyid;
        }
        return $root;
    }
}
