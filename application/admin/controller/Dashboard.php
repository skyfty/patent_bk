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
            'business'=>0,
            'genearch'=>0,
        ];
        $branchwhere = [];
        if ($branch_info) {
            $branchwhere['branch_model_id'] = $branch_info->id;
        }
        foreach(Statistics::where("field", "quantity")->where($branchwhere)->select() as $k=>$v) {
            $stat[$v['table']] = $v['value'];
        }
        $this->view->assign($stat);

        $seventtime = \fast\Date::unixtime('day', -7);
        $businessdata = $customerlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $bt = strtotime($day." 0:0:0");
            $et = strtotime($day." 23:59:59");
            $customerlist[$day] = model("customer")->where($branchwhere)->where("createtime","between time", [$bt, $et])->count();
            $businessdata[$day] = model("business")->where($branchwhere)->where("createtime","between time", [$bt, $et])->count();
        }

        $accountData = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $bt = strtotime($day." 0:0:0");
            $et = strtotime($day." 23:59:59");
            $accountData[$day] = model("account")->where(function($query)use($branch_info){
                if ($branch_info) {
                    $query->where("reckon_branch_model_id",$branch_info->id);
                }
            })->where('cheque_model_id',33)->where("createtime","between time", [$bt, $et])->sum("money");
        }

        $genreData = [];
        $genres = model("genre")->cache(true)->where("pid", 0)->select();
        foreach($genres as $genre) {
            $genreData[$genre['name']] = model("provider")->where($branchwhere)->where("genre_cascader_id", "in", $genre['children_ids'])->count();
        }


        $this->view->assign([
            'todaycustomer'       => model("customer")->where($branchwhere)->whereTime("createtime","today")->count(),
            'todaybusiness'       => model("business")->where($branchwhere)->whereTime("createtime","today")->count(),
            'todayprovider'       => model("provider")->where($branchwhere)->whereTime("createtime","today")->count(),
            'todaygenearch'       => model("genearch")->where($branchwhere)->whereTime("createtime","today")->count(),
            'businessdata'          => $businessdata,
            'customerlist'       => $customerlist,
            'genredata'       => $genreData,
            'accountdata'       => $accountData,
        ]);
        return $this->view->fetch();
    }

}
