<?php

namespace app\common\model;

use think\Model;

class Claim extends  Cosmetic
{
    // 表名
    protected $name = 'claim';

    public $keywordsFields = [];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CL%06d", $maxid);
        });

        $updateCustomer = function($row){
            $principal_model_ids = self::where("customer_model_id", $row->customer_model_id)->column("principal_model_id");
            $principal_model_ids = array_unique($principal_model_ids);
            model("customer")->where("id", $row->customer_model_id)->update([
                'principal_model_ids'=>implode(",", $principal_model_ids)
            ]);
        };
        self::afterInsert($updateCustomer);self::afterDelete($updateCustomer);

        self::afterInsert(function($row){
            if (!$row->customer->claim_model_id) {
                $row->customer->save(['claim_model_id'=>$row['id']]);
            }
        });

        self::afterDelete(function($row){
            if ($row->customer->claim_model_id == $row['id']) {
                $row->customer->save(['claim_model_id'=>0]);
            }
        });
    }

    public function customer() {
        return $this->hasOne('customer','id','customer_model_id')->joinType("LEFT")->field('*')->setEagerlyType(0);
    }

    public function genearch() {
        return $this->hasOne('principal','id','principal_model_id')->joinType("LEFT")->field('*')->setEagerlyType(0);
    }

}
