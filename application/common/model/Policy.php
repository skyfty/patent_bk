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
        'industry',
        'commission'
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

        self::afterDelete(function($row){
            model("actualize")->where("policy_model_id", $row['id'])->delete();
            model("ordinal")->where("policy_model_id", $row['id'])->delete();
            model("project")->where("policy_model_id", $row['id'])->delete();
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
    public function commission()
    {
        return $this->hasManyComma('commission','id','commission_model_id');
    }
    public function relevance()
    {
        return $this->morphOne('provider', 'provider_model');
    }

    public function industry() {
        return $this->hasManyComma('industry','id','industry_model_id');
    }

    public function condition() {
        $conditions = [];
        $ordinals = $this->ordinals()->select();

        foreach($ordinals as $ordinal) {
            if ($ordinal["type"] == "pre") {
                $condition[] = $ordinal['content'];
            } else {
                $syllable = $ordinal->syllable;
                if($syllable->type == "sql") {
                    $condition[] = $ordinal['content'];
                } else {
                    $condition[] = build_where_param($ordinal['condition'],$ordinal->syllable->name,$ordinal['content']);
                }
            }
        }
        $conditions = implode(" and ", $conditions);
        return $conditions;
    }

    public function match_principal($where = []) {
        $industry_wehre = [];
        if ($this['principalclass'] == "company") {
            foreach(explode(",",  $this['industry_model_id']) as $industrys_id) {
                $industry_wehre[] = build_where_param("FINDIN", 'industry_model_id', $industrys_id);
            }
            $industry_wehre = implode(" OR ", $industry_wehre);
        }

        $principal_ids = model("principal")->with($this['principalclass'])
            ->where("substance_type",$this['principalclass'])
            ->where($this->condition())->where($where)->where(function($query)use($industry_wehre){
                $query->where($industry_wehre);
            })->column("principal.id");
        return $principal_ids;
    }

    public function match() {
        model("actualize")->where("policy_model_id", $this['id'])->delete();
        $data = [];
        foreach($this->match_principal() as $v) {
            $data[] = [
                "principal_model_id"=>$v,
                "policy_model_id"=>$this['id'],
            ];
        }
        model("actualize")->saveAll($data);
    }
}

