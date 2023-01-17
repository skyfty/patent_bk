<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

class Payconfirm extends \app\common\model\Payconfirm
{

    protected static function init()
    {

        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['owners_model_id'] = $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }

    public function confirm() {
        $inflow_cheque_id = $this->cheque->inflow->id;
        $data = [
            "cheque_model_id"=>$inflow_cheque_id,
            "money"=>$this->money,
            "reckon_type"=>$this->cheque->inflow_table,
            "reckon_model_id"=>$this->reckon_model_id,
            "status"=>"locked",
            "description"=>$this->description,
        ];
        $result = self::instance()->add($data);
        if ($result) {
            $this->status = "locked";
            $this->confirmtime = time();
            $this->isUpdate(true)->save();
        }
        return $result;
    }
}
