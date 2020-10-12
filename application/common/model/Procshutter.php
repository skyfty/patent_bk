<?php

namespace app\common\model;

use think\Model;

class Procshutter extends Cosmetic
{
    // 表名
    protected $name = 'procshutter';
    public $keywordsFields = ["name", "idcode"];
    public $append=['download_text'];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CO%06d", $maxid);
        });
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }


    public function getDownloadTextAttr($value,$data) {
        if (!$value && !isset($data['file'])) {
            return "";
        }
        $info = pathinfo($data['file']);
        return $data['name'].".".$info['extension'];
    }
    public function promotion() {
        return $this->hasOne('promotion','id','promotion_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function shuttering() {
        return $this->hasOne('shuttering','id','shuttering_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function procedure() {
        return $this->hasOne('procedure','id','procedure_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
