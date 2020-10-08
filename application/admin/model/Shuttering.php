<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Log;
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

        self::beforeInsert(function($row){
           if (!$row['species_cascader_id']) {
               $row['species_cascader_id'] = $row->procedure->species_cascader_id;
           }
        });
    }

    public function getDownloadTextAttr($value,$data) {
        if (!$value && !(isset($data['file']) && $data['file'])) {
            return "";
        }
        if ($data['type'] == "image") {
            return model("fields")->get($data['file'],[], true)->title;
        }
        $info = pathinfo($data['file']);
        return $data['name'].".".$info['extension'];
    }
    public function getFileAttr($value,$data) {
        if (!$value && !(isset($data['file']) && $data['file'])) {
            return "";
        }
        if ($data['type'] == "image") {
            return "";
        }
        return $data['file'];
    }
}
