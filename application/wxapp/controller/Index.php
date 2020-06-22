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

    public function register($openid, $user_info) {
        $customer = model("customer");
        $wxuser = $this->app->user->get($openid);
        if ($wxuser) {
            $customer->name = $wxuser->nickname;
            $customer->sex = $wxuser->sex;
//            $customer->avatar = $this->downloadheadimgurl($wxuser->headimgurl);
        } else {
            $customer->name = "匿名";
        }
        $customer->wxapp_openid = $openid;
        $customer->branch_model_id = 0;
        $customer->owners_model_id =$customer->creator_model_id = 2;
        $customer->save();
    }

    public function login() {
        if ($this->auth->isLogin()) {
            $this->success(__('Login successful'), ['user_id' => $this->auth->id,'token' => $this->auth->getToken()]);
        }
        if ($this->request->has("code") && $this->request->has("user_info")) {
            $session_key = $this->app->mini_program->sns->getSessionKey($this->request->param("code"));
            if (!$session_key) {
                $this->error(__('You have no permission'));
            }
            $openid = $session_key->openid;

            $result = $this->auth->wxlogin($openid);
            if ($result !== true) {
                $user_info = json_decode($this->request->param("user_info"), true);
                Log::info($user_info);
                $this->register2($openid, $user_info);
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
