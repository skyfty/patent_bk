<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Equipment;

/**
 * 首页接口
 */
class Checkmac extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     * 
     */
    public function index()
    {
        $mid = $this->request->param("mid");
        if (!$mid) {
            $this->error("Invalid params");
        } else {
            $where = [
                "machine_id"=>$mid,
            ];
            $row = model("equipment")->where($where)->find();
            if (!$row) {
                $this->error(__('No Results were found'));
            }
            $classroom = $row->classroom;
            $data = [
                "name"=>$row->name,
                "school"=>[
                    "name"=>$row->branch->name,
                    "id"=>$row->branch_model_id
                ],
                "classroom"=>[
                    'name'=>$classroom->name,
                    'id'=>$row->classroom_model_id
                ],
                "machine_id"=>$row->machine_id,
            ];
            $this->success("success", $data);
        }
    }
}
