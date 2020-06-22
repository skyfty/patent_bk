<?php

namespace app\wxapp\controller;

use app\common\model\Fields;
use app\customer\model\WechatResponse;
use think\Db;
use app\common\controller\Wechat;
use app\common\library\Sms as Smslib;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class User extends Wxapp
{
    public function edit($id = null)
    {
        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error("参数错误");
        }
        $result = $this->user->customer->allowField(true)->save($params);
        if ($result !== false) {
            $this->success();
        } else {
            $this->error($this->user->customer->getError());
        }
    }

    public function view() {
        if (!$this->user) {
            $this->error(__('Please login first'), -1);
        }
        $this->success("OK", $this->user->customer);
    }

    public function adviser() {
        $this->success();
    }
}
