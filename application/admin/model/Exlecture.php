<?php

namespace app\admin\model;

use think\Model;
use app\admin\library\Auth;

class Exlecture extends \app\common\model\Exlecture
{

    protected static function init()
    {

        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });

        parent::init();

        self::afterDelete(function($row){
            self::destroy(["pid"=>$row['id']], true);
            Expound::destroy(['exlecture_model_id'=>$row['id']], true);
        });

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }

            $row['phase'] = "";
            $prt = self::get(['id'=>$row['pid']]);
            if ($prt) {
                $row['phase'] = $prt['phase'].",";
            }
            $row['phase'].=$row['name'];
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
            'duration'=>$data['duration'],
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

}
