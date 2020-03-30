<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 大纲管理
 *
 * @icon fa fa-circle-o
 */
class Division extends Cosmetic
{
    
    /**
     * Division模型对象
     * @var \app\admin\model\Division
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Division;
    }

    public function classtree() {
        $where = array();
        $where['procedure_model_id'] = $this->request->param("procedure_model_id");
        $list =collection($this->model->where($where)->with("chapters")->select())->toArray() ;

        $chequelList = [];
        foreach ($list as $k => $v) {
            $chequelList[] = [
                'id'     => $v['id'],
                'parent' => '#',
                'name'   => $v['chapters']['name'],
                'type'   => "list",
                'state'  => ['opened' => false]
            ];
        }
        return $chequelList;
    }

    protected function spectacle($model) {
        return $model;
    }
}
