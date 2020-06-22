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


    private function downloadheadimgurl($url) {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        if (!$res || $res->getStatusCode() != "200") {
            return false;
        }
        $tempPath = tempnam(sys_get_temp_dir(), 'kindle_img');
        file_put_contents($tempPath, $res->getBody());
        return $this->upload_file($tempPath);
    }


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

    private function updateuser($row, $user_info) {
        $row->name = $user_info['nickName'];
        $row->sex = $user_info['gender'];
        $row->avatar = $this->downloadheadimgurl($user_info['avatarUrl']);
        $row->branch_model_id = 0;
        $row->owners_model_id =$row->creator_model_id = 2;
        return $row;
    }

    public function register($openid, $user_info) {
        $customer = model("customer");
        $row = $customer->where("wxapp_openid", $openid)->find();
        if ($row) {
            $this->updateuser($row, $user_info);
            $row->create();
        } else {
            $this->updateuser($customer, $user_info);
            $customer->wxapp_openid = $openid;
            $customer->save();
        }
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
                $this->register($openid, $user_info);
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
