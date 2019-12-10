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
    protected $selectpageFields = ['idcode','name', 'id', 'status'];
    protected $searchFields = ['name', 'idcode','slug'];

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, []);
        parent::_initialize();
        $this->model = model("promotion");

    }

}
