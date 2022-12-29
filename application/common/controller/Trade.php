<?php

namespace app\common\controller;

use app\admin\library\Auth;
use think\Config;
use think\Controller;
use think\Hook;
use think\Lang;
use think\Loader;
use think\Session;
use fast\Tree;

/**
 * 后台控制器基类
 */
class Trade extends Controller
{

    /**
     * 布局模板
     * @var string
     */
    protected $layout = 'default';

    /**
     * 权限控制类
     * @var Auth
     */
    protected $auth = null;
    protected $noNeedLogin = [];

    /**
     * 模型对象
     * @var \think\Model
     */
    protected $model = null;


    /**
     * 是否开启Validate验证
     */
    protected $modelValidate = false;

    protected $searchFields = ['name', 'idcode'];

    protected $modelSceneValidate = true;
    protected $dataLimit = false;
    protected $dataLimitFieldAutoFill = true;
    protected $dataLimitField = "owners_model_id";

    protected $relationSearch =[];
    /**
     * 引入后台控制器的traits
     */
    use \app\common\library\traits\Backend;
    use \app\common\library\traits\Buildparam;

    public function _initialize()
    {
        $modulename = $this->request->module();
        $controllername = Loader::parseName($this->request->controller());
        $actionname = strtolower($this->request->action());

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;

        // 定义是否Addtabs请求
        !defined('IS_ADDTABS') && define('IS_ADDTABS', input("addtabs") ? true : false);

        // 定义是否AJAX请求
        !defined('IS_AJAX') && define('IS_AJAX', $this->request->isAjax());

        !defined('IS_IFRAME') && define('IS_IFRAME', input("iframe") ? TRUE : FALSE);

        // 定义是否Dialog请求
        !defined('IS_DIALOG') && define('IS_DIALOG', input("dialog") ? true : false);

        $this->auth = Auth::instance();

        // 设置当前请求的URI
        $this->auth->setRequestUri($path);

        //检测是否登录
        if (!$this->auth->match($this->noNeedLogin) && !$this->auth->isLogin()) {
            Hook::listen('admin_nologin', $this);
            $url = Session::get('referer');
            $url = $url ? $url : $this->request->url();
            if ($url == '/') {
                $this->redirect('index/login', [], 302, ['referer' => $url]);
                exit;
            }
            $this->error(__('Please login first'), url('index/login', ['url' => $url]));
        }

        // 如果有使用模板布局
        if ($this->layout) {
            $this->view->engine->layout('layout/' . $this->layout);
        }

        // 语言检测
        $lang = strip_tags($this->request->langset());

        $site = Config::get("site");

        $upload = \app\common\model\Config::upload();

        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['name', 'indexurl', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'jsname'         => 'trade/' . str_replace('.', '/', $controllername),
            'moduleurl'      => rtrim(url("/{$modulename}", '', false), '/'),
            'language'       => $lang,
            'fastadmin'      => Config::get('fastadmin'),
            'referer'        => Session::get("referer")
        ];
        $config = array_merge($config, Config::get("view_replace_str"));

        Config::set('upload', array_merge(Config::get('upload'), $upload));

        // 配置信息后
        Hook::listen("config_init", $config);
        //加载当前控制器语言包
        $this->loadlang($controllername);
        //渲染站点配置
        $this->assign('site', $site);
        //渲染配置信息
        $this->assign('config', $config);
        //渲染权限对象
        $this->assign('auth', $this->auth);
        //渲染管理员对象
        $this->assign('admin', Session::get('admin'));
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name)
    {
        Lang::load(APP_PATH . $this->request->module() . '/lang/' . $this->request->langset() . '/' . str_replace('.', '/', $name) . '.php');
    }

    protected function spectacle($model) {
        return $model;
    }

    protected function getDataLimitAdminIds() {
        return null;
        $filter = $this->request->get("filter", '');
        $filter = (array)json_decode($filter, TRUE);
        if (isset($filter['owners_model_id']) || isset($filter['creator_model_id'])) {
            return null;
        }
        if ($this->auth->isSuperAdmin()) {
            return null;
        }
        $adminIds = [];
        if (in_array($this->dataLimit, ['auth', 'personal'])) {
            $adminIds = $this->dataLimit == 'auth' ? $this->auth->getChildrenAdminIds(true) : [$this->auth->id];
        }
        return $adminIds;

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
