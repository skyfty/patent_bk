<?php

namespace app\admin\model;

use think\Model;
use app\admin\library\Auth;

class Lecture extends  \app\common\model\Lecture
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
            Courseware::destroy(['lecture_model_id'=>$row['id']], true);
        });

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);
    }

    public function coursewares()
    {
        return $this->hasMany('courseware','lecture_model_id');
    }

    public function getTreeNodeAttr() {
        $data = $this->getData();
        $lectureitem =  [
            'name'   => $data['name'],
            'childOuter'=>false,
            'type'=>$data['type'],
            'status'=>$data['status'],
            'id' => $data['id'],
            'iconSkin' => $data['type'],
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


    public function getUniqueList()
    {
        return ['0' => "默认", '1' => "唯一"];
    }

    public function getUniqueTextAttr($value, $data)
    {
        $value = $value ? $value : $data['unique'];
        $list = $this->getUniqueList();
        return isset($list[$value]) ? $list[$value] : '';
    }
}
