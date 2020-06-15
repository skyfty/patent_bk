<?php

namespace app\admin\controller\wxapp;

use app\common\controller\Backend;
use app\common\model\WechatResponse;

/**
 * 微信自动回复管理
 *
 * @icon fa fa-circle-o
 */
class Navbar extends Backend
{

    protected $model = null;
    protected $noNeedRight = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('WxappNavbar');
    }

    /**
     * 编辑
     */
    public function index()
    {
        $row = $this->model->get("10001");
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $db = $this->model->getQuery();
                $db->startTrans();
                try {
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $db->commit();
                        $this->success("", null, $row->toArray());
                    } else {
                        $db->rollback();
                        $this->error($row->getError());
                    }
                } catch (\think\Exception $e) {
                    $db->rollback();
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
