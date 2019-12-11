<?php

namespace app\customer\controller;

use app\customer\library\Auth;
use Symfony\Component\HttpFoundation\Session\Session;
use think\Config;
use think\Controller;
use think\Hook;
use app\common\model\WechatAutoreply;
use app\common\model\WechatContext;
use app\common\model\WechatResponse;
use app\common\model\WechatConfig;
use EasyWeChat\Foundation\Application;
use app\common\library\Wechat as WechatService;
use think\Log;
use think\Request;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Customer extends Controller
{
    protected $user = null;
    protected $model = null;
    protected $relationSearch = null;

    protected $layout = 'layout/default';

    protected $noNeedLogin = [];

    protected $noNeedRight = [];

    protected $auth = null;

    protected $app = null;

    protected $searchFields = [];

    use \app\common\library\traits\Buildparam;
    use \app\common\library\traits\Backend;

    // 初始化
    public function __construct()
    {
        parent::__construct();

        $hostname = $this->request->server('SERVER_NAME');
        $this->app = new Application(Config::get('wechat'));

        //移除HTML标签
        $this->request->filter('strip_tags');
        $modulename = $this->request->module();
        $controllername = strtolower($this->request->controller());
        $actionname = strtolower($this->request->action());

        // 如果有使用模板布局
        if ($this->layout) {
            $this->view->engine->layout($this->layout);
        }
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', \think\Session::get('token')));

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;

        $this->auth = Auth::instance();

        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        $this->auth->keeptime(3600);

        // 检测是否需要验证登录
        if (!$this->request->isAjax() && !$this->auth->match($this->noNeedLogin)) {
            //初始化
            $this->auth->init($token);
            //检测是否登录
            if (!$this->auth->isLogin()) {
                if ($this->request->has("code")) {
                    $params = [];
                    $uid = $this->request->param("uid",'');
                    if ($uid) {
                        $params['uid'] = $uid;
                    }

                    $sharedUrl = url("/index/shared", $params);
                    $user = $this->app->oauth->user();
                    if (!$user) {
                        $this->redirect($sharedUrl);
                    }
                    $openid = $user->getId();
                    $result = $this->auth->wxlogin($openid);
                    if ($result !== true) {
                        $this->redirect($sharedUrl);
                    }
                } else {
                    $this->redirect("/index/login?url=".$this->request->url());
                }
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    $this->error(__('You have no permission'));
                }
            }
        } else {
            // 如果有传递token才验证是否登录状态
            if ($token) {
                $this->auth->init($token);
            }
        }

        if ($this->auth->isLogin()) {
            $this->user = $this->auth->getUser();
        }
        $this->assign('user', $this->user);

        // 语言检测
        $lang = strip_tags($this->request->langset());
        $site = Config::get("site");
        $upload = \app\common\model\Config::upload();


        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['name', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'jsname'         => $modulename.'/' . str_replace('.', '/', $controllername),
            'moduleurl'      => rtrim(url("/{$modulename}", '', false), '/'),
            'language'       => $lang,
        ];
        $config = array_merge($config, Config::get());
        Config::set('upload', array_merge(Config::get('upload'), $upload));

        // 配置信息后
        Hook::listen("config_init", $config);

        if (!$this->request->isAjax()) {
            if (!isset($config['wxsdk'])) {
                $config['wxsdk'] = $this->saveWxJsdkConfig();
            }
        }
        $this->assign('title', $config['wechat']['title']);

        //渲染站点配置
        $this->assign('auth', $this->auth);
        $this->assign('site', $site);
        $this->assign('config', $config);

        // 设定主题模板目录
        $this->view->engine->config('view_path', $this->view->engine->config('view_path') . $config['theme'] . DS);

        // 加载自定义标签库
        $this->view->engine->config('taglib_pre_load', 'app\customer\taglib\Cms');
    }


    public function saveWxJsdkConfig() {
        try{
            $wxconfig = $this->app->js->config([
                'checkJsApi',
                'chooseImage',
                'openLocation',
                'getLocation',
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'hideMenuItems',
                'showMenuItems',
            ], false);
            $wxconfig = json_decode($wxconfig, true);
        }catch (\Exception $e) {
            $wxconfig = [];
        }
        return $wxconfig;
    }

    public function wxhandle($message) {
        $WechatService = new WechatService;
        $WechatContext = new WechatContext;
        $WechatResponse = new WechatResponse;

        $openid = $message->FromUserName;
        $to_openid = $message->ToUserName;
        $event = $message->Event;
        $eventkey = $message->EventKey ? $message->EventKey : $message->Event;

        $unknownmessage = WechatConfig::value('default.unknown.message');
        $unknownmessage = $unknownmessage ? $unknownmessage : "";

        switch ($message->MsgType) {
            case 'event': //事件消息
                switch ($event) {
                    case 'subscribe'://添加关注
                        $subscribemessage = WechatConfig::value('default.subscribe.message');
                        $subscribemessage = $subscribemessage ? $subscribemessage : "欢迎关注我们!";
                        return $subscribemessage;
                    case 'unsubscribe'://取消关注
                        return '';
                    case 'LOCATION'://获取地理位置
                        return '';
                    case 'VIEW': //跳转链接,eventkey为链接
                        return '';
                    default:
                        break;
                }

                $response = $WechatResponse->where(["eventkey" => $eventkey, 'status' => 'normal'])->cache(true)->find();
                if ($response) {
                    $content = (array)json_decode($response['content'], TRUE);
                    $context = $WechatContext->where(['openid' => $openid])->find();
                    $data = ['eventkey' => $eventkey, 'command' => '', 'refreshtime' => time(), 'openid' => $openid];
                    if ($context) {
                        $WechatContext->data($data)->where('id', $context['id'])->update();
                        $data['id'] = $context['id'];
                    } else {
                        $id = $WechatContext->data($data)->save();
                        $data['id'] = $id;
                    }
                    $result = $WechatService->response($this, $openid, $content, $data);
                    if ($result) {
                        return $result;
                    }
                }
                return $unknownmessage;
            case 'text': //文字消息
            case 'image': //图片消息
            case 'voice': //语音消息
            case 'video': //视频消息
            case 'location': //坐标消息
            case 'link': //链接消息
            default: //其它消息
                //上下文事件处理
                $context = $WechatContext->where(['openid' => ['=', $openid], 'refreshtime' => ['>=', time() - 1800]])->cache(true)->find();
                if ($context && $context['eventkey']) {
                    $response = $WechatResponse->where(['eventkey' => $context['eventkey'], 'status' => 'normal'])->cache(true)->find();
                    if ($response) {
                        $WechatContext->data(array('refreshtime' => time()))->where('id', $context['id'])->update();
                        $content = (array)json_decode($response['content'], TRUE);
                        $result = $WechatService->command($this, $openid, $content, $context);
                        if ($result) {
                            return $result;
                        }
                    }
                }
                //自动回复处理
                if ($message->MsgType == 'text') {
                    $wechat_autoreply = new WechatAutoreply();
                    $autoreply = $wechat_autoreply->where(['text' => $message->Content, 'status' => 'normal'])->cache(true)->find();
                    if ($autoreply) {
                        $response = $WechatResponse->where(["eventkey" => $autoreply['eventkey'], 'status' => 'normal'])->cache(true)->find();
                        if ($response) {
                            $content = (array)json_decode($response['content'], TRUE);
                            $context = $WechatContext->where(['openid' => $openid])->cache(true)->find();
                            $result = $WechatService->response($this, $openid, $content, $context);
                            if ($result) {
                                return $result;
                            }
                        }
                    }
                }
                return $unknownmessage;
        }
    }
    public function wxpayhandle($notify) {
        return true;
    }
    /**
     * 微信API对接接口
     */
    public function wxapi()
    {
        $self = $this;
        $this->app->server->setMessageHandler(function ($message) use($self) {
            return call_user_func(array($self, 'wxhandle'), $message);
        });
        $response = $this->app->server->serve();
        // 将响应输出
        $response->send();
    }

    /**
     * 微信API对接接口
     */
    public function wxpay()
    {
        $self = $this;
        $response = $this->app->payment->handleNotify(function ($notify, $successful) use($self) {
            if (!$successful) {
                Log::error($notify);
                return true;
            }
            return call_user_func(array($self, 'wxpayhandle'), $notify);
        });
        // 将响应输出
        $response->send();
    }
    /**
     * 登录回调
     */
    public function callback()
    {

    }

    public function qrcode($uid,$key = "UID") {
        $sceneValue = $key."_".$uid;
        $result = $this->app->qrcode->temporary($sceneValue, 6 * 24 * 3600);
        if ($result) {
            $url = $this->app->qrcode->url($result->ticket);
            $this->assign("qrcodeurl", $url);
        }
        return $this->view->fetch("common/qrcode");
    }

    /**
     * 获取数据限制的管理员ID
     * 禁用数据限制时返回的是null
     * @return mixed
     */
    protected function getDataLimitAdminIds()
    {
        return null;
    }

    public function _empty($name)
    {
        if ($this->request->isAjax() && $name == "index") {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model->where($where)->with($this->relationSearch)->order($sort, $order)->limit($offset, $limit)->select();
            $this->result(collection($list)->toArray(), 1);
        } else {
            return $this->view->fetch($name);
        }
    }

    public function avatar($ids) {
        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        try {
            $result = $this->model->allowField(true)->save($params);
            if ($result !== false) {
                $this->success();
            } else {
                $this->error($this->user->genearch->getError());
            }
        } catch (\think\exception\PDOException $e) {
            $this->error($e->getMessage());
        }
    }


    /**
     * 渲染配置信息
     * @param mixed $name  键名或数组
     * @param mixed $value 值
     */
    protected function assignconfig($name, $value = '')
    {
        $this->view->config = array_merge($this->view->config ? $this->view->config : [], is_array($name) ? $name : [$name => $value]);
    }
}
