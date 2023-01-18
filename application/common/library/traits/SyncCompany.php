<?php
namespace app\common\library\traits;

trait SyncCompany
{
    public function syncCompany($ids) {
        $row = $this->model->get($ids);
        if ($row === null)
            $this->error(__('Params error!'));
        $companyInfo = $row->company;
        $claim = model("claim")->where("principal_model_id", $row->company->principal_model_id)->find();
        if ($claim != null) {
            $companyInfo['customer'] = $claim->customer;
        }
        $this->result($companyInfo, 1);
    }


}