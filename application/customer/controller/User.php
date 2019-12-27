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

    public function adviser() {
        $adviser = $this->user->customer->adviser;
        if (!$adviser || $adviser->persionalqrcode == "") {
            $branch_model_id = $this->user->customer->branch_model_id;
            $authGroup = model("AuthGroup")->where("branch_id", $branch_model_id)->where("name",'课程顾问')->find();
            if (!$authGroup) {
                $this->redirect(url("page/index",['id'=>48]));
            }
            $advisers = model("staff")->where("branch_model_id", $this->user->customer->branch_model_id)->where("persionalqrcode","neq", "")->where($authGroup->id, "exp", "FIND_IN_SET(".$authGroup->id.",quarters)")->select();
            if (!$advisers || count($advisers) == 0) {
                $this->redirect(url("page/index",['id'=>48]));
            }
            $adviser = $advisers[rand(0, count($advisers) -1)];
            $this->user->customer->save(['adviser_model_id'=>$adviser->id]);
        }
        $this->view->assign("adviser", $adviser);
        return $this->view->fetch();
    }
}
