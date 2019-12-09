<?php

namespace app\customer\controller;

use think\Db;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Provider extends Customer
{
    protected $noNeedLogin = ['evaluate'];

    public function __construct() {
        parent::__construct();
        $this->model = model("provider");
        $this->relationSearch =  ["staff", "customer", "promotion"];
    }

    public function index() {
        $provider = $this->model->with($this->relationSearch);
        $state = $this->request->param("state", '');
        if ($state == "") {
            $this->error(__('Param error'));
        }
        $params = ['state'=>$state];
        $id = $this->request->param("id", "");
        if (!$id) {
            $claims = $this->user->genearch->claims;
            if (count($claims) == 0) {
                $this->view->assign("jumpurl", "/genearch/adviser");
                return $this->view->fetch("student/nostudent");
            }
            $id = $claims[0]['customer_model_id'];
        }
        if ($id) {
            $params['id'] = $id;
            $provider->where("provider.customer_model_id", $id);
        }
        if ($state == 4) {
            if (!$id) {
                $id = $this->user->genearch->customer_ids;
            }
            $customer = \app\customer\model\Customer::get($id);
            if (!$customer) {
                $this->error(__('Param error'));
            }
            $recent_courses = $customer->recent_unstarted_courses;
            if ($recent_courses) {
                $provider->where("provider.starttime", $recent_courses['starttime'])->where("provider.endtime", $recent_courses['endtime']);
            }
        }else if ($state == 5) {
            $provider->where("provider.state", 'in',['5','6']);
        } else {
            $provider->where("provider.state", $state);
        }

        $list = $provider->order("provider.evaluatetime desc")->paginate(10, false, ['type' => '\\app\\common\\library\\Bootstrap']);;
        $list->appends($params);
        $this->view->assign("__PAGELIST__", $list);
        $state_list = [
            '4'=>'待授课',
            '1'=>'未开始',
            '5'=>'已完成',
        ];
        $this->view->assign("state_list", $state_list);
        return $this->view->fetch();
    }
    public function leave($id) {
        $provider = $this->model->get($id);
        if (!$provider) {
            $this->error(__('Param error'));
        }
        $provider->leave();
        $this->success();
    }

    public function presignin($id) {
        $provider = $this->model->get($id);
        if (!$provider) {
            $this->error(__('Param error'));
        }
        if ($provider['checkwork'] == 1) {
            $this->success("已经签到过了");
        } else {
            $provider->presignin();
            $this->success("智慧点+2");
        }
    }

    public function evaluate($id) {
        $row = $this->model->get($id);
        if (!$row) {
            $this->error(__('Param error'));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}
