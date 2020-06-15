<?php

namespace app\wxapp\controller;
use app\common\model\Fields;
use think\Request;


/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Aptitude extends Wxapp
{
    protected $layout = 'aptitude/layout';

    // 初始化
    public function __construct()
    {
        parent::__construct();
        $this->model = model('aptitude');
    }


}
