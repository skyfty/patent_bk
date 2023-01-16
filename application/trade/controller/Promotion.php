<?php

namespace app\trade\controller;

use app\admin\model\Scenery;
use app\admin\model\Sight;
use think\Config;

/**
 * 政策管理
 *
 * @icon fa fa-circle-o
 */
class Promotion extends Trade
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\trade\model\Promotion;
    }


    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        if (!$this->request->isAjax()) {
            $this->assignFields();
        }
        return parent::index();
    }

    protected function assignFields() {
        $scenery = Scenery::get(['model_table' => "promotion",'pos'=>'index'],[],true);
        $fields =  Sight::with('fields')->where(['scenery_id'=>$scenery['id']])->where("fields.name", "not in",[
            "branch",
        ])->order("weigh", "asc")->cache(true)->select();;
        $this->view->assign('fields', $fields);
    }
    protected function spectacle($model) {
//        $branch_model_id = $this->request->param("branch_model_id");
//        if ($branch_model_id == null) {
//            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
//                return $model;
//            }
//        }
//        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;
//
//        $model->where("policy.branch_model_id", $branch_model_id);

        return $model;
    }
}
