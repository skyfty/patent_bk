<?php

namespace app\admin\controller;

class Admin extends auth\Admin
{
    protected $selectpageFields = ['id', 'username'];
    protected $searchFields = ['username'];
    protected $selectpageShowFields = ['username'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model->with('staff');
    }

    /**
     * 下拉搜索
     */
    protected function selectpage($searchfields = null)
    {
        $this->dataLimit = 'auth';
        $this->dataLimitField = 'id';
        return parent::selectpage($searchfields);
    }
}
