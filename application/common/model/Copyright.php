<?php

namespace app\common\model;

use think\Model;

class Copyright extends Professional
{
    // 表名
    protected $name = 'copyright';
    public $keywordsFields = ["name", "idcode"];

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
    public function runningSoftware() {
        return $this->hasOne('osystem','id','running_software_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function developOs() {
        return $this->hasOne('osystem','id','develop_os_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function developTool() {
        return $this->hasOne('dtool','id','develop_tool_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function auxiliarySoftware() {
        return $this->hasOne('osystem','id','auxiliary_software_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function dlanguage() {
        return $this->hasOne('dlanguage','id','dlanguage_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

}
