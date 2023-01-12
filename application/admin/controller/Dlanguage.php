<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 开发语言
 *
 * @icon fa fa-circle-o
 */
class Dlanguage extends Cosmetic
{
    protected $selectpageShowFields = ['name'];


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Dlanguage;
    }


    public function generateCode() {
        $ids= $this->request->request("ids");
        if ($ids === null)
            $this->error(__('Params error!'));
        $row = $this->model->get($ids);
        if ($row === null)
            $this->error(__('Params error!'));

        $code = $row->generateCode();
        $this->result($code, 1);
    }

}
