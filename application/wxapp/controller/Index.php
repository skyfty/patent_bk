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
        $url = $this->request->get('url', 'index/index');
        if ($this->auth->isLogin()) $this->redirect($url);

        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];

            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true) {
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }
        $this->view->assign('redirect_url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }
}
