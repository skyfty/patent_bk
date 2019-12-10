<?php

namespace app\common\behavior;
use think\Config;
use EasyWeChat\Foundation\Application;

class Provider
{
    private function send($row,$message, $template) {


    }

    public function newprovider(&$row) {
        if (Config::get('wechat.notify') == 1 && $row->notify == 1) {
            $staff = $row->staff;
            $message = [
            ];
            $this->send($row, $message, __FUNCTION__);
        }
    }

    public function accomplish(&$row)
    {
        if (Config::get('wechat.notify') == 1 && $row->notify == 1) {
            $message = [
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
            $message = [
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
