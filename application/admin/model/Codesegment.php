<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;
use traits\model\SoftDelete;

class Codesegment extends  \app\common\model\Codesegment
{

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        $beforeupdate = function($row){
            $row['code'] = trim($row['code']);
            $arr = explode("\n", $row['code']);
            $row['lines_cnt'] = count($arr);
            $row['name'] = substr($row['code'], 0, 300);
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);

        $updateStatistics = function($row){

            $row->dlanguage->updateStatistics();
        };
        self::afterInsert($updateStatistics);self::afterUpdate($updateStatistics);self::afterDelete($updateStatistics);

    }

}
