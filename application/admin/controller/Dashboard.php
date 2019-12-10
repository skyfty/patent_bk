<?php

namespace app\admin\controller;

use app\admin\model\Statistics;
use app\common\controller\Backend;
use think\Session;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{
    protected $noNeedRight = ['index'];
    public function index()
    {
        $branch_info = null;
        $this->admin = Session::get('admin');
        if ($this->admin && $this->admin['staff_id'] &&($this->staff = \app\admin\model\Staff::get(['admin_id'=>$this->admin['id']]))) {
            $branch_info = $this->staff->branch;
        }
        $this->view->assign('branch_info', $branch_info);

        $stat = [
            'customer'=>0,
            'provider'=>0,
        ];
        $branchwhere = [];
        if ($branch_info) {
            $branchwhere['branch_model_id'] = $branch_info->id;
        }
        foreach(Statistics::where("field", "quantity")->where($branchwhere)->select() as $k=>$v) {
            $stat[$v['table']] = $v['value'];
        }
        $this->view->assign($stat);



        return $this->view->fetch();
    }

}
