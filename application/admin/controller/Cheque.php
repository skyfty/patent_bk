<?php

namespace app\admin\controller;

use app\admin\model\Modelx;
use app\common\controller\Backend;

/**
 * 账目类型
 *
 * @icon fa fa-circle-o
 */
class Cheque extends Backend
{
    /**
     * Cheque模型对象
     * @var \app\common\model\Cheque
     */
    protected $model = null;
    protected $selectpageShowFields = ['name','description',];
    protected $noNeedRight = ['classtree'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Cheque');
        $this->view->assign('molddata', \app\common\model\Cheque::getMoldList());
        $this->view->assign("typeList", Modelx::where(['accountswitch'=>1])->cache(true)->column('id,name'));
    }


    public function classtree() {
        $where = array();
        $type = $this->request->param("reckon_table");
        if ($type) {
            $where['reckon_table'] = array("in", explode(",",$type));
        }
        $list = $this->model->cache(true)->where($where)->select();

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
