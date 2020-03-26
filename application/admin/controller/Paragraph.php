<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 段落管理
 *
 * @icon fa fa-paragraph
 */
class Paragraph extends Cosmetic
{
    
    /**
     * Paragraph模型对象
     * @var \app\admin\model\Paragraph
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Paragraph;
    }


    protected function spectacle($model) {
        return $model;
    }
}
