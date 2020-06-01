<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

class Behavior extends  \app\common\model\Behavior
{
    // 追加属性
    use \app\admin\library\traits\Adjective;


    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("BE%06d", $maxid);
            $row['warehouse_model_id'] = \app\admin\model\Assembly::get($row['assembly_model_id'])->warehouse_model_id;
        });

        self::beforeUpdate(function($row){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['assembly_model_id'])) {
                $row['warehouse_model_id'] = \app\admin\model\Assembly::get($row['assembly_model_id'])->warehouse_model_id;
            }
        });

        self::afterUpdate(function($row){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['type']) || isset($changeData['content'])) {
                $expounds = model("expound")->where("primary_warehouse_model_id|second_warehouse_model_id|third_warehouse_model_id|entire_warehouse_model_id", $row->warehouse_model_id)->select();
                foreach($expounds as $expound) {
                    $expound->save(['detail'=>($row->warehouse_model_id.",behavior,".$changeData['content'])]);
                }
            }
        });
    }
    protected function setAdjectiveAttr($value,$data)
    {
        if (!is_string($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }
        return $value;
    }
}
