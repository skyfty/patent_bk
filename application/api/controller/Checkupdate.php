<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 */
class Checkupdate extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function index() {

        foreach(['bleacher','student','staff'] as $apk) {
            $appObj  = new \apk\ApkParser();
            $apkFile = ROOT_PATH . '/public/uploads/eidolon/app-'.$apk.'-release.apk';
            $res = $appObj->open($apkFile);
            if ($res) {
                $config = [
                    "Code"=>0,
                    "Msg"=>"",
                    "VersionCode"=> $appObj->getVersionCode(),
                    "VersionName"=>$appObj->getVersionName(),
                    "UpdateStatus"=>1,
                    "ModifyContent"=>"稳定性修复",
                    "DownloadUrl"=>"http://t.touchmagic.cn/uploads/eidolon/app-".$apk."-release.apk",
                    "ApkSize"=>filesize($apkFile),
                    "ApkMd5"=>md5_file($apkFile),
                ];
                var_dump($config);
                file_put_contents(ROOT_PATH . '/public/uploads/eidolon/'.$apk.'.json', json_encode($config,JSON_UNESCAPED_UNICODE ));
            }
        }

    }
}
