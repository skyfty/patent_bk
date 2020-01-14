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
            $sceneryList = [];
            $sceneryWhere = array_merge(['model_id' => $cosmeticModel->id, 'pos' => array("in", $this->viewScenerys)]);
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

}
