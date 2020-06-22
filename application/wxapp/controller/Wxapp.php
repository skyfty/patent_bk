<?php

namespace app\wxapp\controller;
use app\wxapp\library\Auth;
use EasyWeChat\Foundation\Application;
use Symfony\Component\HttpFoundation\Session\Session;
use think\Config;
use think\Controller;
use think\exception\HttpResponseException;
use think\Hook;
use think\Request;
use think\Response;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Wxapp extends Controller
{
    protected $user = null;
    protected $model = null;
    protected $relationSearch = null;

    protected $noNeedLogin = [];

    protected $noNeedRight = [];

    protected $auth = null;

    protected $app = null;

    protected $searchFields = [];

    /**
     * 默认响应输出类型,支持json/xml
     * @var string
     */
    protected $responseType = 'json';

    use \app\common\library\traits\Backend;

    // 初始化
    public function __construct()
    {
        parent::__construct();

        $wechat_config = Config::get('wechat');
        $wechat_config['debug'] = \think\Config::get('app_debug');
        $wechat_config['log'] = [
        'level'      => 'debug',
        'permission' => 0777,
        'file'       => 'easywechat.log',
        ];
        $wechat_config['mini_program'] = [
            'app_id'   => $wechat_config['wxappid'],
            'secret'   => $wechat_config['wxappsecret'],
            'token'    => $wechat_config['token'],
            'aes_key'  => $wechat_config['aes_key'],
        ];
        $this->app = new Application($wechat_config);

        //移除HTML标签
        $this->request->filter('strip_tags');
        $modulename = $this->request->module();
        $controllername = strtolower($this->request->controller());
        $actionname = strtolower($this->request->action());

        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', \think\Session::get('token')));

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;

        $this->auth = Auth::instance();

        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        $this->auth->keeptime(3600);

        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //初始化
            $this->auth->init($token);

            //检测是否登录
            if (!$this->auth->isLogin()) {
                $this->error(__('You have no permission'), -1);
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    $this->error(__('You have no permission'), -1);
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
    }


    /**
     * 操作成功返回的数据
     * @param string $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为1
     * @param string $type   输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 1, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 操作失败返回的数据
     * @param string $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function error($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型，支持json/xml/jsonp
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result($msg, $data = null, $code = 0, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];
        // 如果未设置类型则自动判断
        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
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
