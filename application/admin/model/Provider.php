<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Config;
use think\Db;
use think\Exception;
use think\Hook;

class Provider extends \app\common\model\Provider
{
    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }

    public function expire() {
    }


    public function dispatch() {
        $cutime = time();
        $spanstarttime = round(($this['starttime'] - $cutime) / 60);
        $spanendtime = round(($this['endtime'] - $cutime) / 60);

        if ($this['state'] == 1 && $spanstarttime <= 0) {
            //当超过课程开始时间时，自动将状态修改为“授课中”。
            $this['state'] = 4;
        }

        if ($this['state'] == 1 && $this['prenotifytime'] == 0 && $spanstarttime > 0) {
            $prenotify = Config::get('wechat.prenotify');
            if ($prenotify > 0 && $spanstarttime <= $prenotify) {
                $this['prenotifytime'] = $cutime;
                Hook::listen('newprovider',$this);
            }
        }

        if ($spanendtime < 0 && $this['state'] <= 5) {
            if ($cutime > strtotime("23:30:00") && $cutime < strtotime("23:59:59")) {
                if (in_array($this['checkwork'], ['3', '1'])) {
                    Checkwork::create([
                        'customer_model_id' => $this['customer_model_id'],
                        'provider_model_id' => $this['id'],
                        'status' => -1,
                    ]);
                    $this['checkwork'] = 0;
                }
            }
        }


        if ($spanendtime < 0 && $this['state'] < 5) {
            //课程订单状态，当订单状态为“授课中”时，当超过课程结束时间时，自动将状态修改为“已完成”。并扣除“课次数”1次，并增加“智慧点”10个
            if ($this['state'] == 4){
                if ($this['checkwork'] == 2) {
                    Wisdom::create([
                        'customer_model_id'  => $this['customer_model_id'],
                        'provider_model_id' => $this['id'],
                        'status' =>  1,
                        'amount' => 10,
                    ]);
                    $this->increaseScholarship();
                    $this->countLores();
                }

                if ($this['checkwork'] == 2 || (in_array($this['checkwork'], ['0','4']) && $this->customer->break_appointment_count == 0)) {
                    $this->decreaseSellotape();
                }
            }
            $this['state'] = 5;
            Hook::listen('accomplish',$this);
        }
        $this->save();
    }
}
