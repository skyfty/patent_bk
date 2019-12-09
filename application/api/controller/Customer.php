<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 */
class Customer extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function index()
    {
        $id = $this->request->param("id");
        if (!$id) {
            $this->error("Invalid params");
        }

        $equ = model("customer")->where("id", $id)
            ->field([
                "id",
                "name",
                "idcode",
                "sex",
                "nickname",
                "birthday",
                "wisdom"])
            ->find();
        if ($equ) {
            $courses = model("provider")->where("customer_model_id", $equ['id'])->where("state",1)->select();
            $equ['courses'] = collection($courses)->visible([
                'id',
                'idcode',
                'checkwork'])
                ->toArray();

            $this->success("success", $equ);
        } else {
            $this->error("not found");
        }
    }
}
