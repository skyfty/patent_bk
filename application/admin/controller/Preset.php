<?php

namespace app\admin\controller;


/**
 * 预置流程
 *
 * @icon fa fa-circle-o
 */
class Preset extends Cosmetic
{
    
    /**
     * Preset模型对象
     * @var \app\admin\model\Preset
     */
    protected $model = null;
    protected $multiFields = 'status,animation_id';

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, ['update']);
        parent::_initialize();
        $this->model = new \app\admin\model\Preset;
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
                'templet_model_id'=>$this->request->param("templet_model_id"),
                'prelecture_model_id'=>$this->request->param("prelecture_model_id"),
            ];
        }
        $this->view->assign("row", $row);

        return $this->view->fetch();
    }

}
