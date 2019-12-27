<?php

namespace app\customer\controller;

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
class User extends Customer
{
    protected $layout = 'user/layout';

    public function agreement()
    {
        $content = WechatResponse::get(3,[],true)->content;
        if ($content) {
            $content = json_decode($content);
            $this->view->assign("content", $content->content);
        }
        return $this->view->fetch();
    }

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
}
