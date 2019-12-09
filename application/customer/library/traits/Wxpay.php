<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\customer\library\traits;

trait Wxpay
{
    private function prepare($openid, $out_trade_no, $total_fee, $product_id, $body) {
        $order = new \EasyWeChat\Payment\Order([
            'openid'      => $openid,
            'out_trade_no'      => $out_trade_no,
            'total_fee'         => $total_fee * 100,
            'body'              => $body,
            'product_id'        => $product_id,
            'time_start'        => date("YmdHis"),
            'time_expire'       => date("YmdHis", time() + 600),
            'trade_type'        => \EasyWeChat\Payment\Order::JSAPI,
        ]) ;
        return $this->app->payment->prepare($order);
    }
}