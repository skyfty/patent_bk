<?php

namespace app\trade\controller;

use app\common\controller\Backend;

/**
 * 开发语言
 *
 * @icon fa fa-circle-o
 */
class Dlanguage extends Trade
{
    protected $selectpageShowFields = ['name'];


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\trade\model\Dlanguage;
    }
    use \app\common\library\traits\GeneralCode;
}
