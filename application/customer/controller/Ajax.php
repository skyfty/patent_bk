<?php

namespace app\customer\controller;

use fast\Random;
use think\Cache;
use think\Config;
use think\Controller;
use think\Db;
use think\Lang;

/**
 * Ajax异步请求接口
 * @internal
 */
class Ajax extends Controller
{
    protected $noNeedLogin = ['lang'];
    protected $noNeedRight = ['*'];
    protected $layout = '';
    protected $auth = [];

    use \app\common\library\traits\Upload;
    use \app\common\library\traits\Ajax;

    public function _initialize()
    {
        parent::_initialize();

        $this->auth['id'] = 4;

        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);
    }

    /**
     * 加载语言包
     */
    public function lang()
    {
        header('Content-Type: application/javascript');
        return jsonp(Lang::get(), 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }
}
