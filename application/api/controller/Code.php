<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 */
class Code extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function index()
    {

    }
}
