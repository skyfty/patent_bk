<?php

namespace app\customer\controller;

use think\Db;
use think\Exception;
use think\Log;

/**
 * CMS首页控制器
 * Class Index
 * @package app\wechat\controller
 */
class Trade extends Customer
{
    public function __construct() {
        parent::__construct();
        $this->model = model("trade");
    }

    public function spectacle($model) {
        $genearch = $this->user->genearch;
        $state = $this->request->param("state", "all");
        if (in_array($state, ['unpay','refund','payed'])) {
            $model->where('paystatus',$state)->where('status','neq', 'cancel');
        }
        if (in_array($state, ['used','unused'])) {
            $model->where('usestatus',$state)->where('status', 'done');
        }
        if (in_array($state, ['cancel','closed','done','buyying'])) {
            $model->where('status',$state);
        }
        $model->where("genearch_model_id",$genearch->id );
        return $model;
    }

    public function index() {
        if ($this->request->isAjax()) {
            return parent::index();
        }
        $state = $this->request->param("state", "all");
        $this->view->assign("state", $state);

        $state_list = [
            'all'=>'全部',
            'unpay'=>'待付款',
            'refund'=>'已退款',
            'unused'=>'可使用',
            'used'=>'已使用',
            'payed'=>'已付款',
            'cancel'=>'已取消',

        ];
        $this->view->assign("state_list", $state_list);
        return $this->view->fetch();
    }

    public function cancel($id) {
        $row = $this->model->find($id);
        if (!$row)
            $this->error(__('No Results were found'));
        $result = $row->save(['status'=>'cancel']);
        if ($result === false) {
            $this->error($this->model->getError());
        }
        $this->success("订单已经取消");

    }

    public function view($id) {
        $row = $this->model->find($id);
        if (!$row)
            $this->error(__('No Results were found'));

        $genearch = $this->user->genearch;
        if ($row->buying_open_model_id) {
            $shared = [
                "desc"=>$row->commodity->summary,
                "title"=>$row->commodity->name,
                "imgUrl"=>$row->commodity->imgurl,
                "link"=>url('buying/join', ['id'=>$row->buying_open_model_id])
            ];
            $this->assignConfig("shared", $shared);
        }
        $this->view->assign("customer_ids", $genearch->customer_ids);
        $this->view->assign("row", $row);
        return $this->view->fetch($row->buying_open_model_id?"buying":"view");
    }

    public function add() {
        $commodity_id = $this->request->param("commodity_id");
        if (!$commodity_id) {
            $this->error(__('Params error!'));
        }
        $commodity = model("commodity")->get($commodity_id);
        if (!$commodity) {
            $this->error(__('No Results were found'));
        }
        $amount = $this->request->param("amount", 0);
        if ($amount <= 0) {
            $this->error("购买个数必须大于0");
        }
        if ($this->request->isPost()) {
            $db = $this->model->getQuery();
            $db->startTrans();
            try {
                $genearch = $this->user->genearch;
                $data = [
                    'name'=>$commodity->name."单独购买订单",
                    "genearch_model_id"=>$genearch->id,
                    "branch_model_id"=>$genearch->branch_model_id,
                    "commodity_model_id"=>$commodity_id,
                    "unit"=>$commodity['price'],
                    "telephone"=>$genearch->telephone,
                    "amount"=>$amount,
                    "price"=>$amount * $commodity['price'],
                    "sum_presell"=>$amount * $commodity['amount'],
                    "pay"=>'wechat',
                    "status"=>'new',
                    "paystatus"=>'unpay',
                    "usestatus"=>'unused',
                    "type"=>'normal',
                ];
                $trade = \app\customer\model\Trade::create($data);
                if ($trade === false) {
                    throw new \think\Exception($this->model->getError());
                }
                $prepare = $trade->prepare();
                if ($prepare == false) {
                    throw new \think\Exception("生成订单失败");
                }
                if ('FAIL' === $prepare->return_code) {
                    throw new \think\Exception($prepare->return_msg);
                }
                $db->commit();
                $trade['unifiedOrder'] = $this->app->payment->configForJSSDKPayment($prepare['prepay_id']);
                $this->success("订单生成成功", null, $trade->toArray());
            } catch (\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        } else {
            $this->view->assign("amount", $amount);
            $this->view->assign("commodity", $commodity);
            return $this->view->fetch();
        }
    }

    public function pay($id) {
        $row = $this->model->find($id);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($row['status'] == "cancel") {
            throw new \think\Exception($row->buyingopen->status != "locked"?"拼团活动已经结束了":"订单已经取消， 无法完成支付");
        }

        if ($row['type'] == "opening") {
            if (time() >= $row->buyingopen->commodity->getData('endtime')) {
                throw new \think\Exception("拼团活动已经结束了");
            }
        } else if ($row['type'] == "join") {
            if (time() >= $row->buyingopen->getData('endtime')) {
                throw new \think\Exception("拼团活动已经结束了");
            }
        }
        $prepare = $row->prepare();
        if ($prepare == false) {
            throw new \think\Exception("生成订单失败");
        }
        if ('FAIL' === $prepare->return_code) {
            throw new \think\Exception($prepare->return_msg);
        }
        $row['unifiedOrder'] = $this->app->payment->configForJSSDKPayment($prepare['prepay_id']);
        $this->success("订单生成成功", null, $row->toArray());
    }

    public function consume($id) {
        $row = $this->model->find($id);
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost()) {
            $customer_model_id = $this->request->param("customer_model_id");
            if (!$customer_model_id) {
                $this->error(__('Params error!'));
            }
            try {
                if ($row->status != "done") {
                    $this->error("此订单还没有完成");
                }
                if ($row->customer_model_id) {
                    $this->error("此订单已经消耗");
                }

                $result = $row->consume($customer_model_id, "wechat");
                if ($result === false) {
                    $this->error($row->getError());
                } else {
                    $this->success("订单已经消耗");
                }
            } catch (\think\Exception $e) {
                $this->error($e->getMessage());
            }

        } else {
            $customers = model("customer")->with($this->relationSearch)->where("id","in", function($query){
                $query->table("__CLAIM__")->where("genearch_model_id", $this->user->genearch->id)->field("customer_model_id");
            })->select();
            if (count($customers) <= 0) {
                $this->view->assign("jumpurl", "/genearch/adviser");
                return $this->view->fetch("student/nostudent");
            }
            $this->view->assign("customers", $customers);
            $this->view->assign("row", $row);
            return $this->view->fetch();
        }
    }
}
