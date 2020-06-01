<?php

namespace app\admin\controller;

/**
 * 课程流程
 *
 * @icon fa fa-circle-o
 */
class Expound extends Cosmetic
{
    
    /**
     * Expound模型对象
     * @var \app\admin\model\Expound
     */
    protected $model = null;
    protected $multiFields = 'status,animation_id';

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, ['update']);
        parent::_initialize();
        $this->model = new \app\admin\model\Expound;
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
                'promotion_model_id'=>$this->request->param("promotion_model_id"),
                'exlecture_model_id'=>$this->request->param("exlecture_model_id"),
            ];
        }
        $this->view->assign("row", $row);

        return $this->view->fetch();
    }
}
