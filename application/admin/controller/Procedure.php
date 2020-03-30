<?php

namespace app\admin\controller;

use app\admin\model\Modelx;
use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\common\controller\Backend;
use think\App;

/**
 * 业务步骤
 *
 * @icon fa fa-circle-o
 */
class Procedure extends Cosmetic
{
    protected $selectpageFields = ['name', 'idcode', 'id', 'relevance_model_type', 'species_cascader_id'];
    protected $selectpageShowFields = ['name','idcode'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Procedure;
    }

    public function shuttering() {
        $ids =$this->request->param("ids", null);
        if ($ids === null)
            $this->error(__('Params error!'));

        $cosmeticModel = Modelx::get(['table' => "procedure"],[],!App::$debug);
        if (!$cosmeticModel) {
            $this->error('未找到对应模型');
        }
        $row = $this->getModelRow($ids);
        if ($row === null)
            $this->error(__('No Results were found'));

        $this->view->assign("row", $row);
        if ($row['type'] == "shuttering") {
            $scenery = Scenery::get(['model_table' => "procedure",'name'=>$this->request->action(),'pos'=>'view'],[],!App::$debug);
            $where =array(
                'scenery_id'=>$scenery['id']
            );
            $fields =  Sight::with('fields')->where($where)->order("weigh", "asc")->cache(!App::$debug)->select();;
            $content = $this->view->fetch();
        } else {
            $fields = [];
            $content = $this->view->fetch("division");
        }
        return array("content"=>$content, "fields"=>$fields, "row"=>$row);

    }

    public function classtree() {
        $where = array();
        $where['relevance_model_type'] = $this->request->param("relevance_model_type");
        $list = $this->model->where($where)->select();

        $chequelList = [];
        foreach (collection($list)->toArray() as $k => $v) {
            $chequelList[] = [
                'id'     => $v['id'],
                'parent' => '#',
                'text'   =>$v['name'],
                'type'   => "link",
            ];
        }
        return $chequelList;
    }


    public function preview() {
        $row = $this->model->where("id",$this->request->param("id"))->find();
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);
        return $this->view->fetch();

    }
}
