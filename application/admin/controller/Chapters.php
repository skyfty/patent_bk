<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 章节管理
 *
 * @icon fa fa-circle-o
 */
class Chapters extends Cosmetic
{
    
    /**
     * Chapters模型对象
     * @var \app\admin\model\Chapters
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Chapters;
    }


    protected function spectacle($model) {
        return $model;
    }

}
