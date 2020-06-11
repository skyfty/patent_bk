<?php

namespace app\customer\controller;

use think\Request;
use think\Response;

/**
 * 步骤文档
 *
 * @icon fa fa-circle-o
 */
class Procshutter extends Customer
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\customer\model\Procshutter;
    }

    public function edit($id = null) {
        $relevances = $this->model->where("relevance_model_type", $this->request->param("relevance"))->where("relevance_model_id", $id)->select();
        if (!$relevances) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $rows = $this->request->param("rows/a");

            $db = $this->model->getQuery();
            $db->startTrans();
            try {
                foreach($rows as $idcode=>$row) {
                    $this->model->where("idcode", $idcode)->update(["file"=>$row['file']]);
                }
                $db->commit();
                $this->success("成功");
            } catch(\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        }
        $this->view->assign("rows", $relevances);
        $this->view->assign('refere_url', Request::instance()->server('HTTP_REFERER'));
        return $this->view->fetch();
    }
}
