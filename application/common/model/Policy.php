<?php

namespace app\common\model;
use app\admin\library\Auth;

use think\Model;
use traits\model\SoftDelete;

class Policy extends  Cosmetic
{
    // 表名
    protected $name = 'policy';
    public $keywordsFields = ["name", "idcode"];
    public $append = [
        'industry'
    ];

    protected static function init()
    {
        parent::init();

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PO%06d", $maxid);
        });
    }


    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function ordinals()
    {
        return $this->hasMany('Ordinal','id','policy_model_id');
    }

    public function projects()
    {
        return $this->hasMany('Project','id','policy_model_id');
    }

    public function species()
    {
        return $this->hasOne('species','id','species_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function relevance()
    {
        return $this->morphOne('provider', 'provider_model');
    }

    public function industry() {
        return $this->hasManyComma('industry','id','industry_model_id');
    }
}

