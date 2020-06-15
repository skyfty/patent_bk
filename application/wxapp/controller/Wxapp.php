<?php

namespace app\wxapp\controller;

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
class Wxapp extends Controller
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

        //移除HTML标签
        $this->request->filter('strip_tags');
        $modulename = $this->request->module();
        $controllername = strtolower($this->request->controller());
        $actionname = strtolower($this->request->action());

        // 如果有使用模板布局
        if ($this->layout) {
            $this->view->engine->layout($this->layout);
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
