<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 文章管理
 *
 * @icon fa fa-circle-o
 */
class Article extends Cosmetic
{
    
    /**
     * Article模型对象
     * @var \app\admin\model\Article
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Article;
    }



    protected function spectacle($model) {
        return $model;
    }

}
