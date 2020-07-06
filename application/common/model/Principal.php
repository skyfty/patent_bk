<?php

namespace app\common\model;

use think\App;
use think\Loader;
use think\Model;

class Principal extends  Cosmetic
{
    // 表名
    protected $name = 'principal';
    public $keywordsFields = ["name", "idcode"];
    public $append = [];

    protected function initialize()
    {
        parent::initialize();
        if (isset($this['principalclass']) &&  $this['principalclass']) {
            $this->assignTimestampFieldConvert($this['principalclass']['model_type']);
        }
    }

    protected static function init()
    {
        parent::init();

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                try {
                    $row['slug'] = \fast\Pinyin::get($row['name']);
                } catch(\Exception $e) {
                }
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PR%06d", $maxid);
        });

        self::afterInsert(function($row){
            $row['substance_type'] = $row['principalclass']['model_type'];
            $substance = model($row['substance_type'])->create(['principal_model_id'=>$row['id']]);
            $row['substance_id'] = $substance['id'];
            $row->save();
        });
        self::afterDelete(function($row){
            model($row['principalclass']['model_type'])->where(['principal_model_id'=>$row['id']])->delete();
            model("claim")->where(['principal_model_id'=>$row['id']])->delete();
            model("actualize")->where("principal_model_id", $row['id'])->delete();
            model("quarters")->where("principal_model_id", $row['id'])->delete();
        });

        self::afterUpdate(function($row){
            $row->match();
        });
    }

    public function principalclass() {
        return $this->hasOne('principalclass','id','principalclass_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function substance()
    {
        return $this->morphTo();
    }

    public function company() {
        return $this->hasOne('Company','id','substance_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function persion() {
        return $this->hasOne('Persion','id','substance_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function industry() {
        return $this->hasManyComma('industry','id','industry_model_id');
    }
    public function promotions()
    {
        return $this->hasMany('promotion','principal_model_id');
    }

    public function actualizes()
    {
        return $this->hasMany('actualize','principal_model_id');
    }

    public function match() {
        model("actualize")->where("principal_model_id", $this['id'])->delete();
        $data = [];
        $policys = model("policy")->where("principalclass",$this['substance_type'])->select();
        foreach($policys as $policy) {
            $principal_ids = $policy->match_principal(['principal.id'=>$this['id']]);
            if ($principal_ids && in_array($this['id'], $principal_ids)) {
                $data[] = [
                    "principal_model_id"=>$this['id'],
                    "policy_model_id"=>$policy['id'],
                ];
            }
        }
        model("actualize")->saveAll($data);
    }


    public function amount() {
        //支出
        $where = [
            "reckon_type"=>$this->name,
            "reckon_model_id"=>$this->id
        ];
        $data = [];
        $data['payamount'] = Account::hasWhere('cheque',['mold'=>-1])->where($where)->sum("money");
        $data['incomeamount'] = Account::hasWhere('cheque',['mold'=>1])->where($where)->sum("money");
        $data['balance'] = $data['incomeamount'] - $data['payamount'];
        $data['cash'] = $data['balance'];

        $this->isUpdate(true)->allowField(true)->save($data);
    }

}
