<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 流程课件
 *
 * @icon fa fa-circle-o
 */
class Courseware extends Cosmetic
{
    
    /**
     * Courseware模型对象
     * @var \app\admin\model\Courseware
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Courseware;
        $this->dataLimit = false;

    }
    use \app\admin\library\traits\Preset;
    /**
     * 添加
     */
    public function edit($ids = NULL) {
        $row = $this->model->get($ids);
        if (!$row) {
            $row = [
                'lecture_model_id'=>$this->request->param("lecture_model_id"),
            ];
        }
        $this->view->assign("row", $row);

        return $this->view->fetch();
    }
}
