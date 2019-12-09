<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;

trait Selfedit
{

    /**
     * 编辑
     */
    public function edit($ids = NULL)  {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        $branch_model_id = $this->admin['staff_id']?$this->staff->branch_model_id:null;
        if ($branch_model_id != null) {
            if ($row['branch_model_id'] != $branch_model_id) {
                $this->error(__('You have no permission'));
            }
        }
        return parent::edit($ids);
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids) {
            $branch_model_id = $this->admin['staff_id']?$this->staff->branch_model_id:null;
            if ($branch_model_id != null) {
                $ids = explode(",", $ids);
                $aids = $this->model->where("id", 'in', $ids)->where("branch_model_id", $branch_model_id)->column("id");
                $ids = implode(",", array_intersect_assoc($ids, $aids));
                if (!$ids) {
                    $this->error(__('You have no permission'));
                }
            }
        }
        parent::del($ids);
    }

}