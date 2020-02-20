<?php

namespace app\admin\controller;

use app\admin\model\Scenery;
use app\admin\model\Sight;
/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */

class Customer extends Cosmetic
{
    protected $selectpageFields = ['name', 'idcode', 'slug', 'id', 'status'];
    protected $searchFields = ['name', 'idcode', 'slug'];
    protected $selectpageShowFields = ['name', 'idcode'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("customer");
    }

    public function account($ids) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);

        $scenery = Scenery::get(['model_table' => 'account', 'name'=>"customer"],[],true);
        $where =array(
            'scenery_id'=>$scenery['id'],
            "fields.name"=>array("not in", array("weigh",'reckon','reckon_type'))
        );
        $fields =  Sight::with('fields')->where($where)->order("weigh", "DESC")->cache(true)->select();;
        $content = $this->view->fetch();
        return array("content"=>$content, "fields"=>$fields);
    }


    public function syncavatar($ids) {
        $row = $this->model->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $result = $row->save(['avatar'=>$row['faceimage']]);
        if ($result !== false) {
            $this->success();
        } else {
            $this->error($row->getError());
        }
    }


    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("customer.branch_model_id", $branch_model_id);

        return $model;
    }
}
