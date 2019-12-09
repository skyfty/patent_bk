<?php

namespace app\admin\model;

use app\admin\library\Auth;

class Promotion extends  \app\common\model\Promotion
{
    // 追加属性
    protected $append = [
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("PN%06d", $maxid);
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        });

        self::beforeUpdate(function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        });

        self::afterDelete(function($row){
            Provider::destroy(['appoint_promotion_model_id'=>$row['id']]);
        });


    }
}
