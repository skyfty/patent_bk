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

        $updateGenearch = function($row){
            $customer_model_ids = self::where("genearch_model_id", $row->genearch_model_id)->column("customer_model_id");
            $customer_model_ids = array_unique($customer_model_ids);
            model("genearch")->where("id", $row->genearch_model_id)->update([
                'customer_ids'=>implode(",", $customer_model_ids)
            ]);
        };
        self::afterInsert($updateGenearch);self::afterDelete($updateGenearch);

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
        return $this->hasOne('genearch','id','genearch_model_id')->joinType("LEFT")->field('*')->setEagerlyType(0);
    }

}
