<?php
namespace app\common\library\traits;

trait Produce
{
    public function produce($ids) {
        $row = $this->model->where("id",$ids)->find();
        if (!$row)
            $this->error(__('No Results were found'));

        $where = [];
        $procedure_ids = $this->request->param("procedure_ids/a");
        if ($procedure_ids) {
            $where['id']=["in",$procedure_ids ];
        }
        $procedures = model("procedure")->where("relevance_model_type",strtolower($this->model->raw_name))->where($where)->select();
        foreach($procedures as $procedure) {
            $row->produceDocument($procedure);
        }
        $this->success();
    }


}