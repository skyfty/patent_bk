<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;
use EasyWeChat\Foundation\Application;
use think\Config;
class Branch extends \app\common\model\Branch
{
    // 追加属性

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['owners_model_id'] = $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::beforeUpdate(function($row){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['app_id'])) {
                $app = new Application($row['app_id'] ? $row->wechat : Config::get('wechat'));
                $sceneValue = "BID_" . $row['id'];
                $result = $app->qrcode->forever($sceneValue);
                if ($result) {
                    $row->qrcodeimg = $app->qrcode->url($result->ticket);
                }
            }
        });
        self::afterDelete(function($row){
            model("AuthGroup")->where("branch_id", $row->id)->delete();
            model("ModelGroup")->where("branch_model_id", $row->id)->delete();
        });
    }
}
