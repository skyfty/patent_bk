<?php

namespace app\admin\controller;

use app\admin\model\Fields;
use app\admin\model\Modelx;
use app\common\model\Genre;


/**
 * 服务项目
 *
 * @icon fa fa-circle-o
 */
class Promotion extends Cosmetic
{
    
    /**
     * Promotion模型对象
     * @var \app\admin\model\Promotion
     */
    protected $model = null;
    protected $selectpageFields = ['idcode','name', 'id', 'status','class_number'];
    protected $searchFields = ['name', 'idcode','slug'];

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, []);
        parent::_initialize();
        $this->model = model("promotion");
        $this->assignconfig("animations", model("animation")->cache(true)->select());

    }


    public function slideshare($ids) {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        $this->view->assign("exlectures", $row->allexlecture());
        $this->view->assign("row", $row);
        return $this->view->fetch("../../common/view/slideshare");
    }
}
