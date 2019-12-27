<?php

namespace app\customer\controller;

use think\Db;
use think\Exception;
use think\Log;
use think\Validate;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Index extends Customer
{
    use \app\common\library\traits\Sendsms;
    use \app\common\library\traits\Upload;

    protected $noNeedLogin = ['login', 'wxapi','wxpay', 'wxoauth','sendsms','shared','index'];
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

    private function wxsubscribe($message) {
        $openid = $message->FromUserName;
        $eventkey = $message->EventKey ? $message->EventKey : $message->Event;
        $customer = model("customer")->where("wxopenid",$openid)->find();

        if ($customer) {
            $customer->deletetime = null;
        } else {
            $customer = model("customer");
            $wxuser = $this->app->user->get($openid);
            if ($wxuser) {
                $customer->name = $wxuser->nickname;
                $customer->sex = $wxuser->sex;
                $customer->avatar = $this->downloadheadimgurl($wxuser->headimgurl);
            } else {
                $customer->name = "匿名";
            }
            $customer->wxopenid = $openid;
        }
        $customer->subscribe = "yes";

        $subparam = explode("_", $eventkey);
        if ($subparam && count($subparam) == 3) {

        } else {
            $customer->branch_model_id = 0;
            $customer->owners_model_id =$customer->creator_model_id = 2;
        }
        $customer->save();
    }

    private function wxunsubscribe($message) {
        $openid = $message->FromUserName;
        $customer = model("customer")->where("wxopenid",$openid )->find();
        if ($customer) {
            $customer->save(["subscribe"=>'no']);
        }
    }

    private function wxscan($message) {
        $this->wxsubscribe($message);
    }
    public function wxhandle($message) {
        $event = $message->Event;
        switch ($message->MsgType) {
            case 'event': //事件消息
                switch ($event) {
                    case 'subscribe': {
                        $this->wxsubscribe($message);
                        break;
                    }
                    case 'unsubscribe': {
                        $this->wxunsubscribe($message);
                        break;
                    }
                    case 'SCAN': {
                        $this->wxscan($message);
                        break;
                    }
                }
        }
        return parent::wxhandle($message);
    }
    public function wxpayhandle($notify) {
        $transaction_id = $notify->get('transaction_id');
        if(!$transaction_id){
            return "输入参数不正确";
        }
        $trade = model("trade")->where("idcode", $notify->get('out_trade_no'))->find();
        if (!$trade) {
            return "订单参数无效";
        }
        Db::startTrans();
        try {
            $result = $trade->save(['transaction_id'=>$transaction_id]);
            if (!$result) {
                throw new Exception($trade->getError());
            }
            Db::commit();
            return true;

        } catch (\Exception $e) {
            Log::info($e->getMessage());
            Db::rollback();
            return $e->getMessage();
        }
    }

    public function wxoauth() {
        $url = $this->request->get('url', 'index/index');
        $url = url($url, '', false, true);
        $response = $this->app->oauth->scopes(['snsapi_userinfo'])->redirect($url);
        $this->redirect($response->getTargetUrl());
    }

    public function index() {
        return $this->view->fetch();
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

    public function shared() {
        $uid = $this->request->param("uid", null);
        if ($uid !== null) {
            return parent::qrcode($uid,"UID");
        }
        return parent::qrcode($this->request->param("bid", '2'),"BID");
    }

}
