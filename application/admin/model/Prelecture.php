<?php

namespace app\admin\model;

use think\Model;
use app\admin\library\Auth;

class Prelecture extends  \app\common\model\Prelecture
{

    protected static function init()
    {

        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['owners_model_id'] =  $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
        self::afterDelete(function($row){
            self::destroy(["pid"=>$row['id']], true);
            Preset::destroy(['prelecture_model_id'=>$row['id']], true);
        });

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);
    }

    public function getTreeNodeAttr() {
        $data = $this->getData();
        $lectureitem =  [
            'name'   => $data['name'],
            'childOuter'=>false,
            'type'=>$data['type'],
            'status'=>$data['status'],
            'lecture_id'=>$data['lecture_id'],
            'id'=>$data['id'],
            'iconSkin'=>$data['type'],
        ];
        if ($data['type'] == "lecture") {
            $lectureitem['isParent'] = false;
            $lectureitem['open'] = false;
        } else {
            $lectureitem['isParent'] = true;
            $lectureitem['open'] = true;
        }
        return $lectureitem;
    }


    public function presets()
    {
        return $this->hasMany('preset','prelecture_model_id')->order("weigh desc");
    }

}
