<?php

namespace app\common\behavior;
use think\Config;
use EasyWeChat\Foundation\Application;

class Provider
{
    private function send($row,$message, $template) {
        $claims = model("claim")->where("customer_model_id",$row->customer_model_id)->select();
        foreach($claims as $cla) {
            $genearch = $cla->genearch;
            if ($genearch) {
                $wxopenid = $genearch->wxopenid;
                if ($wxopenid) {
                    $message['touser'] = $wxopenid;
                    $branch = $genearch->branch;
                    if ($branch && $branch[$template."_template_id"]) {
                        $message['template_id'] = $branch[$template."_template_id"];
                    }else {
                        $message['template_id'] = Config::get('wechat.'.$template."_template_id");
                    }

                    if ($branch && $branch["authurl"]) {
                        $message['url'] = str_replace("%authurl%", $branch["authurl"], $message['url']);
                    }else {
                        $url = Config::get('wechat.authurl');
                        $message['url'] = str_replace("%authurl%", $url, $message['url']);
                    }
                    try {
                        $app = new Application($branch && $branch['app_id']? $branch->wechat:Config::get('wechat'));
                        $app->notice->send($message);
                    } catch (\Exception $e) {
                    }
                }
            }
        }

    }

    public function newprovider(&$row) {
        if (Config::get('wechat.notify') == 1 && $row->notify == 1) {
            $staff = $row->staff;
            $message = [
                'url' => 'http://%authurl%/provider/evaluate?id='.$row->id,
                'data' => [
                    "first"=>[
                        "value" =>"您的宝宝".$row->customer->name."即将参加极客思迪的以下课程，请宝宝准时参加。",
                        "color" =>"#173177"
                    ],
                    "keyword1"=>[
                        "value" =>$row->promotion->genre->root_name."-".$row->promotion->name,
                        "color" =>"#173177"
                    ],
                    "keyword2"=>[
                        "value" =>$row->appoint_time." ".$row->appoint_course,
                        "color" =>"#173177"
                    ],
                    "keyword3"=>[
                        "value" =>$row->branch->name." ".$row->classroom->name,
                        "color" =>"#173177"
                    ],
                    "keyword4"=>[
                        "value" =>($staff->nickname?$staff->nickname:$staff->name)."".($staff->telephone?",".$staff->telephone:""),
                        "color" =>"#173177"
                    ],
                    "remark"=>[
                        "value" =>"点击详情，查看授课报告的详细内容。",
                        "color" =>"#173177"
                    ],
                ],
                'miniprogram' => '',
            ];
            $this->send($row, $message, __FUNCTION__);
        }
    }

    public function accomplish(&$row)
    {
        if (Config::get('wechat.notify') == 1 && $row->notify == 1) {
            $message = [
                'url' => 'http://%authurl%/student/index?id=' . $row->customer_model_id,
                'data' => [
                    "first" => [
                        "value" => $row->customer->name . "家长您好， 你孩子预约的" . $row->promotion->name . "课程已经完成了",
                        "color" => "#173177"
                    ],
                    "keyword1" => [
                        "value" => $row->promotion->genre->root_name . "-" . $row->promotion->name,
                        "color" => "#173177"
                    ],

                    "keyword2" => [
                        "value" => $row->customer->name,
                        "color" => "#173177"
                    ],
                    "keyword3" => [
                        "value" => $row->appoint_time . "" . $row->appoint_course,
                        "color" => "#173177"
                    ],
                    "keyword4" => [
                        "value" => $row->branch->name . " " . $row->classroom->name,
                        "color" => "#173177"
                    ],
                    "remark" => [
                        "value" => "点击详情",
                        "color" => "#173177"
                    ],
                ],
                'miniprogram' => '',
            ];
            $this->send($row, $message, __FUNCTION__);

        }
    }

    public function leave(&$params)
    {

    }

    public function evaluate(&$row)
    {
        if (Config::get('wechat.notify') == 1 && $row->notify == 1) {
            $evalinfo = "课堂秩序" .$row->evaluate_order."星,"."知识掌握" .$row->evaluate_lore."星,"."思路开阔" .$row->evaluate_mentality."星";
            $message = [
                'url' => 'http://%authurl%/provider/evaluate?id=' . $row->id,
                'data' => [
                    "first" => [
                        "value" => $row->customer->name . "家长您好， 你孩子的" . $row->promotion->name . "课程已经结课了",
                        "color" => "#173177"
                    ],
                    "keyword1" => [
                        "value" => $row->customer->name,
                        "color" => "#173177"
                    ],

                    "keyword2" => [
                        "value" => $row->promotion->genre->root_name . "-" . $row->promotion->name,
                        "color" => "#173177"
                    ],
                    "keyword3" => [
                        "value" => $evalinfo,
                        "color" => "#173177"
                    ],
                    "remark" => [
                        "value" => "点击详情",
                        "color" => "#173177"
                    ],
                ],
                'miniprogram' => '',
            ];
            $this->send($row, $message, __FUNCTION__);
        }
    }


    public function presignin(&$params)
    {

    }

    public function signin(&$params)
    {

    }
}
