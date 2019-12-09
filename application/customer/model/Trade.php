<?php

namespace app\customer\model;

use think\Hook;
use think\Log;
use think\Model;
use app\admin\library\Auth;

class Trade extends \app\common\model\Trade
{
    // 追加属性
    protected $append = [
        'commodity',
        'status_text',
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;
        });
        parent::init();

        self::afterUpdate(function($row){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['status'])) {
                $buyingopen = $row->buyingopen;
                if ($buyingopen) {
                    if ($changeData['status'] == 'buyying') {
                        if ($row['type'] == "opening") {
                            Hook::listen('openbuying',$row);
                        }elseif ($row['type'] == "join") {
                            Hook::listen('joinbuying',$row);
                        }
                    }
                }

                if ($changeData['status'] == 'done') {
                    Hook::listen('tradedone',$row);
                }
            }
        });
    }

    public function getStatusTextAttr($value, $data) {
        if ($data['status'] == "cancel")
            return "已取消";
        if ($data['paystatus'] == "refund")
            return "已退款";
        if ($data['status'] == "done" && $data['paystatus'] == "payed" && $data['usestatus'] == "unused") {
            return "可使用";
        }
        if ($data['status'] == "done" && $data['usestatus'] == "used")
            return "已使用";
        return $data['paystatus'] == "payed"?"已付款":"待付款";
    }

    public static function getList($list, $tag)
    {
        return $list;
    }

    public function prepare() {
        $genearch = $this->genearch;
        $order = new \EasyWeChat\Payment\Order([
            'openid'      =>    $genearch->wxopenid,
            'out_trade_no'      => $this->idcode,
            'total_fee'         => $this->price * 100,
            'body'              => $this->name,
            'product_id'        => $this->commodity_model_id,
            'time_start'        => date("YmdHis"),
            'time_expire'       => date("YmdHis", time() + 600),
            'trade_type'        => \EasyWeChat\Payment\Order::JSAPI,
        ]) ;
        return Branch::getWechatApp($genearch->branch)->payment->prepare($order);
    }

}

