<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;
use traits\model\SoftDelete;

class Shuttering extends   \app\common\model\Shuttering
{
// 追加属性
    public $append=['download_text'];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }

    public function getDownloadTextAttr($value,$data) {
        if (!$value && !isset($data['file'])) {
            return "";
        }
        $info = pathinfo($data['file']);
        return $data['name'].".".$info['extension'];
    }
}
