<?php

namespace app\wxapp\controller;

use think\Request;


/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Invent extends Wxapp
{
    protected $layout = 'invent/layout';

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
    public function edit($id = null) {
        $principal= model("principal")->get($this->request->param("principal_model_id"));
        if (!$principal) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->param("row/a");
            $db = $this->model->getQuery();
            $db->startTrans();
            try {
                $url_params = ['principal_model_id'=>$principal['id']];
                if ($id) {
                    $row = $this->model->get($id);
                    if (!$row) {
                        $this->error(__('No Results were found'));
                    }
                    $invent = $row->save($params);
                    if ($invent === false) {
                        throw new \think\Exception($this->model->getError());
                    }
                    $url_params['id'] = $row['id'];
                    $url_params['step'] = $this->request->post("step");
                } else {
                    $invent = $this->model->save($params);
                    if ($invent === false) {
                        throw new \think\Exception($this->model->getError());
                    }
                    $url_params['id'] = $this->model['id'];
                    $url_params['step'] = 2;
                }
                $db->commit();

                $this->model->produceDocument(model("procedure")->where("relevance_model_type","invent")->find());

                if ($url_params['step'] == "10") {
                    $this->success("成功", "/principal/index?id=".$principal['id']);
                } else {
                    $this->success("成功", url('invent/edit',$url_params));
                }

            } catch (\think\exception\PDOException $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }catch(\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        }
        $this->view->assign('principal', $principal);
        $this->view->assign('procedures', model("procedure")->where("relevance_model_type", "invent")->where("type", "fields")->order("order asc")->select());
        $this->view->assign('step', $this->request->param("step", 1));
        $refere_url =  $this->request->param("refere_url", Request::instance()->server('HTTP_REFERER'));
        $this->view->assign('refere_url', $refere_url);
        $this->view->assign('id', $id);
        return $this->view->fetch();
    }

}
