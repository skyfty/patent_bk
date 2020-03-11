<?php

namespace app\admin\controller;

use app\admin\model\Modelx;
use app\common\controller\Backend;
use think\App;

/**
 * 业务步骤
 *
 * @icon fa fa-circle-o
 */
class Procedure extends Cosmetic
{
    protected $selectpageFields = ['name', 'idcode', 'id', 'relevance_model_type', 'species_cascader_id'];
    protected $selectpageShowFields = ['name','idcode'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Procedure;
    }


    public function classtree() {
        $where = array();
        $where['relevance_model_type'] = $this->request->param("relevance_model_type");
        $list = $this->model->where($where)->select();

        $chequelList = [];
        foreach (collection($list)->toArray() as $k => $v) {
            $chequelList[] = [
                'id'     => $v['id'],
                'parent' => '#',
                'text'   =>$v['name'],
                'type'   => "link",
            ];
        }
        return $chequelList;
    }
}
