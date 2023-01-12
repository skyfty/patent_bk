<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Random;
use think\addons\Service;
use think\Cache;
use think\Config;
use think\Db;
use think\Lang;

/**
 * Ajax异步请求接口
 * @internal
 */
class Ajax extends Backend
{
    use \app\common\library\traits\Upload;
    use \app\common\library\traits\Ajax;

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);
    }

    /**
     * 加载语言包
     */
    public function lang()
    {
        header('Content-Type: application/javascript');
        $controllername = input("controllername");
        //默认只加载了控制器对应的语言名，你还根据控制器名来加载额外的语言包
        $this->loadlang($controllername);
        return jsonp(Lang::get(), 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }


    /**
     * 清空系统缓存
     */
    public function wipecache()
    {
        $type = $this->request->request("type");
        switch ($type) {
            case 'content' || 'all':
                Cache::clear();
                rmdirs(CACHE_PATH, false);
                if ($type == 'content')
                    break;
            case 'template' || 'all':
                rmdirs(TEMP_PATH, false);
                if ($type == 'template')
                    break;
            case 'addons' || 'all':
                Service::refresh();
                if ($type == 'addons')
                    break;
        }

        \think\Hook::listen("wipecache_after");
        $this->success();
    }

}
