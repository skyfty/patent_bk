<?php

namespace app\admin\model;
use app\admin\library\Auth;

use think\Model;
use traits\model\SoftDelete;

class Policy extends   \app\common\model\Policy
{
    // 追加属性

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::afterDelete(function($row){
            model("ordinal")->where("policy_model_id",$row['id'])->delete();
            model("project")->where("policy_model_id",$row['id'])->delete();
        });
    }

    public function updateCondition() {
        $condition = [];
        $ordinals = $this->ordinals()->select();
        foreach($ordinals as $ord) {
            $syllable = $ord->syllable;
            if ($ord["type"] == "pre") {
                $condition[] = $ord['content'];
            } else {
                if($syllable->type == "sql") {
                    $condition[] = $ord['content'];
                } else {
                    $condition[] = build_where_param($ord['condition'],$syllable['name'],$ord['content']);
                }
            }
        }
        $this->save(['condition'=>json_encode($condition)]);
    }
}

