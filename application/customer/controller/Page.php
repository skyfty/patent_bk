<?php

namespace app\customer\controller;

use think\Config;

/**
 * CMS单页控制器
 * Class Page
 * @package addons\cms\controller
 */
class Page extends Customer
{
    protected $noNeedLogin = ['*'];

    public function index()
    {
        $id =$this->request->param('id', '');
        $page = \app\customer\model\Page::get($id);

        if (!$page || $page['status'] != 'normal') {
            $this->error(__('No specified page found'));
        }
        $this->view->assign("__PAGE__", $page);
        Config::set('cms.title', $page['title']);
        Config::set('cms.keywords', $page['keywords']);
        Config::set('cms.description', $page['description']);
        return $this->view->fetch();
    }

}
