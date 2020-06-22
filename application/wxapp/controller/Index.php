<?php

namespace app\wxapp\controller;

use think\Db;
use think\Exception;
use think\Log;
use think\Validate;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Index extends Wxapp
{
    use \app\common\library\traits\Upload;

    protected $noNeedLogin = ['login','index'];
    protected $layout = 'index/layout';

    public function index() {
        return $this->view->fetch();
    }

    public function page() {
        $this->success(__('Login successful'), ["wxapp"=>"sldkjfl"]);

    }

    public function base() {
        $wxapp_id = $this->request->param("wxapp_id");

        $wxappbar = model("WxappNavbar")->where("id", $wxapp_id)->find();
        if (!$wxappbar) {
            $this->error(__('No Results were found'));
        }
        $this->success(__('Login successful'), ["wxapp"=>$wxappbar]);
    }

    public function login() {
        if ($this->auth->isLogin()) {
            $this->success(__('Login successful'), ['user_id' => $this->auth->id,'token' => $this->auth->getToken()]);
        }
        if ($this->request->has("code")) {
            $session_key = $this->app->sns->getSessionKey($this->request->param("code"));
            if (!$session_key) {
                $this->error(__('You have no permission'));
            }
            $openid = $session_key->getId();
            $result = $this->auth->wxlogin($openid);
            if ($result !== true) {
                $this->register($openid);
                $result = $this->auth->wxlogin($openid);
            }
            if ($result === true) {
                $this->success(__('Login successful'), ['user_id' => $this->auth->id,'token' => $this->auth->getToken()]);
            }
        } else {
            $this->error(__('You have no permission'));
        }
    }
}
