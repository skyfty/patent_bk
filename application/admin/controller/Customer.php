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
    protected $searchFields = ['name', 'idcode', 'slug','nickname'];
    protected $selectpageShowFields = ['name', 'idcode'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("customer");
        $this->relationSearch = array_merge($this->relationSearch, ["genearch"]);
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

    public function recognition($ids) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if (!$params || !$params['face_token']) {
                $this->error(__('Parameter %s can not be empty', ''));
            }

            $group = ($row['branch_model_id']?"customer_branch_".$row['branch_model_id']:"customer");
            $result = \app\common\library\Aip::addUser($params['face_token'], $row['id'], $group);
            if (!$result || $result['error_code'] != 0) {
                $this->error(__("更新人脸库失败: %s", $result['error_msg']));
            }
            $result = $row->allowField(true)->save($params);
            if ($result !== false) {
                $this->result($this->model->get($ids),1);
            } else {
                $this->error($row->getError());
            }
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
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

    public function delface($ids) {
        $row = $this->model->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        $group = ($row['branch_model_id']?"customer_branch_".$row['branch_model_id']:"customer");
        $result = \app\common\library\Aip::faceDelete($row['face_token'], $row['id'], $group);
        if (!$result || ($result['error_code'] != 0 && $result['error_code'] != '223106')) {
            $this->error(__("更新人脸库失败: %s(%d)", $result['error_msg'], $result['error_code']));
        }
        $result = $row->save(['face_token'=>'', 'faceimage'=>'']);
        if ($result !== false) {
            $this->result($this->model->get($ids),1);
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
