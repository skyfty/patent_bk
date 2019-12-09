<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Random;
use think\Cache;
use think\Config;
use think\Db;

/**
 * Ajax异步请求接口
 * @internal
 */
class Aip extends Backend
{
    protected $noNeedRight = ['*'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);
    }


    public function detect($image, $type) {
        if ($type == "URL") {
            $imagepath = ROOT_PATH . '/public' . $image;
            $image = base64_encode(file_get_contents($imagepath));
        } else {
            $prefix = ";base64,";
            $pos = strpos($image, $prefix);
            if ($pos == false) {
                $this->error(__('prefix can not be empty', ''));
            }
            $image = substr($image, $pos + strlen($prefix));
        }
        return \app\common\library\Aip::detect($image);
    }
}
