<?php

namespace app\admin\model;

use think\Model;

use app\admin\library\Auth;

class Templet extends \app\common\model\Templet
{
    public $keywordsFields = ["name", "idcode"];


    protected static function init()
    {

        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['owners_model_id'] =  $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("TE%06d", $maxid);
        });

        self::afterDelete(function($row){
            Prelecture::destroy(['templet_model_id'=>$row['id']]);
            Preset::destroy(['templet_model_id'=>$row['id']]);
        });

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);

    }
}
