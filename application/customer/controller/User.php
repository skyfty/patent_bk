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

    public function bank()  {
        if (!$this->request->isPost()) {
            $this->assign("banklist", Fields::get(['model_table'=>'genearch','name'=>'bankname'],[],true)->content_list);
            return $this->view->fetch();
        }

        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error("参数错误");
        }

        $result = $this->user->genearch->validate("customer.bank")->allowField(true)->save($params);
        if ($result !== false) {
            $this->success();
        } else {
            $this->error($this->user->genearch->getError());
        }
    }
    public function trade()  {
        if (!$this->request->isPost()) {
            return $this->view->fetch();
        }

        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error("参数错误");
        }

        $result = $this->user->genearch->validate("customer.bank")->allowField(true)->save($params);
        if ($result !== false) {
            $this->success();
        } else {
            $this->error($this->user->genearch->getError());
        }
    }
    public function register() {
        if (!$this->request->isPost()) {
            return $this->view->fetch();
        }
        $telephone = $this->request->request("telephone");

        if (!$telephone || !\think\Validate::regex($telephone, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        $userinfo = model("user")->get(['telephone'=>$telephone]);
        if ($userinfo && $userinfo->genearch->id != $this->user->genearch->id) {
            $this->error(__('已被占用'));
        }
        $captcha = $this->request->request("captcha");
        $ret = Smslib::check($telephone, $captcha, "bind");
        if (!$ret) {
            $this->error(__('验证码不正确'));
        }
        try {
            $params = $this->request->post("row/a");
            $params['telephone'] = $telephone;
            $result = $this->user->genearch->allowField(true)->save($params);
            if ($result !== false) {
                $this->success();
            } else {
                $this->error($this->user->genearch->getError());
            }
        } catch (\think\exception\PDOException $e) {
            $this->error($e->getMessage());
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
        }
    }

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
        $result = $this->user->genearch->allowField(true)->save($params);
        if ($result !== false) {
            $this->success();
        } else {
            $this->error($this->user->genearch->getError());
        }

    }
}
