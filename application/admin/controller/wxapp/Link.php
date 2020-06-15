<?php

namespace app\admin\controller\wxapp;

use app\common\controller\Backend;
use app\common\model\WechatResponse;

/**
 * 微信自动回复管理
 *
 * @icon fa fa-circle-o
 */
class Link extends Backend
{

    protected $model = null;
    protected $noNeedRight = ['check_text_unique'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('WechatAutoreply');
    }

}
