<?php

namespace app\admin\controller;

use app\admin\model\Scenery;
use app\admin\model\Sight;

/**
 * 课次卡订单
 *
 * @icon fa fa-circle-o
 */
class Business extends Cosmetic
{
    
    /**
     * Business模型对象
     * @var \app\admin\model\Business
     */
    protected $model = null;
    protected $selectpageFields = ['*'];
    protected $relationSearch = ["presell"];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Business;
    }

    public function account($ids = null) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);

        $scenery = Scenery::get(['model_id' => 95, 'name'=>"payconfirm"],[],true);
        $where =array(
            'scenery_id'=>$scenery['id'],
            "fields.name"=>array("not in", array("weigh","inflow","related","cheque","mold"))
        );
        $fields =  Sight::with('fields')->where($where)->cache(true)->order("weigh", "asc")->select();;
        $content = $this->view->fetch();
        return array("content"=>$content, "fields"=>$fields);
    }


    public function settle($ids = null) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        if ($row['status'] == 'locked')
            $this->success(__("没有发生改变"));

        try {
            $result = $row->settle();;
            if ($result) {
                $this->success();
            } else {
                $this->error("error");
            }
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
        }
    }


    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if (!$branch_model_id) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("business.branch_model_id", $branch_model_id);

        return $model;
    }
}
