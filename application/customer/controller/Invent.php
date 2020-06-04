<?php

namespace app\customer\controller;

use think\Request;


/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Invent extends Customer
{
    protected $layout = 'aptitude/layout';

    // 初始化
    public function __construct()
    {
        parent::__construct();
        $this->model = model('invent');
    }

    public function view() {
        $id =$this->request->param("id", null);
        if ($id === null)
            $this->error(__('Params error!'));

        $row = $this->model->find($id);
        if (!$row)
            $this->error(__('No Results were found'));

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    public function edit($ids = null) {
        $principal= model("principal")->get($ids);
        if (!$principal) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->param("row/a");
            $db = $this->model->getQuery();
            $db->startTrans();
            try {
                $aptitude = $this->model->save($params);
                if ($aptitude === false) {
                    throw new \think\Exception($this->model->getError());
                }
                $db->commit();
                $this->model->produceDocument(model("procedure")->where("relevance_model_type","invent")->find());

                $this->success("成功", "/principal/index?id=".$principal['id']);
            } catch (\think\exception\PDOException $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }catch(\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        }
        $this->view->assign('principal', $principal);
        $this->view->assign('refere_url', Request::instance()->server('HTTP_REFERER'));
        return $this->view->fetch();
    }

}
