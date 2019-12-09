<?php

namespace app\customer\controller;

use think\Config;

/**
 * 文档控制器
 * Class Archives
 * @package addons\cms\controller
 */
class Archives extends Customer
{
    protected $noNeedLogin = ['*'];
    protected $layout = 'archives/layout';

    public function index() {
        $id = $this->request->param('id', '');
        if (!$id) {
            $this->error(__('No specified channel found'));
        }
        $channel = \app\customer\model\Channel::get($id);
        if (!$channel) {
            $this->error(__('No specified channel found'));
        }
        $params = ['filter' => '', 'id' => $channel->id];

        $this->view->assign("__CHANNEL__", $channel);
        $pagelist = \app\customer\model\Archives::alias('a')
            ->where('status', 'normal')
            ->where('deletetime', 'exp', \think\Db::raw('IS NULL'))
            ->field('a.*')
            ->where('channel_id', $channel['id'])
            ->order("weigh", "desc")
            ->paginate(10, true, ['type' => '\\app\\common\\library\\Bootstrap']);
        $pagelist->appends($params);
        $this->view->assign("__PAGELIST__", $pagelist);

        Config::set('cms.title', $channel['name']);
        Config::set('cms.keywords', $channel['keywords']);
        Config::set('cms.description', $channel['description']);
        return $this->view->fetch();
    }

    public function view()
    {
        $id = $this->request->param('id', '');
        $archives = \app\customer\model\Archives::get($id, ['channel']);

        if (!$archives || ($archives['status'] != 'normal') || $archives['deletetime']) {
            $this->error(__('No specified article found'));
        }
        $channel = \app\customer\model\Channel::get($archives['channel_id']);
        if (!$channel) {
            $this->error(__('No specified channel found'));
        }
        $archives->setInc("views", 1);
        $this->view->assign("__ARCHIVES__", $archives);
        $this->view->assign("__CHANNEL__", $channel);
        Config::set('cms.title', $archives['title']);
        Config::set('cms.keywords', $archives['keywords']);
        Config::set('cms.description', $archives['description']);
        $template = preg_replace('/\.html$/', '', $channel['channeltpl']?$channel['channeltpl']:"view");
        return $this->view->fetch($template);
    }

    /**
     * 赞与踩
     */
    public function vote()
    {
        $id = (int)$this->request->post("id");
        $type = trim($this->request->post("type", ""));
        if (!$id || !$type) {
            $this->error(__('Operation failed'));
        }
        $archives = \app\customer\model\Archives::get($id);
        if (!$archives || ($archives['status'] != 'normal') || $archives['deletetime']) {
            $this->error(__('No specified article found'));
        }
        $archives->where('id', $id)->setInc($type === 'like' ? 'likes' : 'dislikes', 1);
        $archives = \app\customer\model\Archives::get($id);
        $this->success(__('Operation completed'), null, ['likes' => $archives->likes, 'dislikes' => $archives->dislikes, 'likeratio' => $archives->likeratio]);
    }
}
