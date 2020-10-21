<?php

namespace app\common\model;

class Aptitude extends Professional
{
    // 表名
    protected $name = 'aptitude';
    public $keywordsFields = ["name", "idcode"];


    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("AP%06d", $maxid);

        });

        self::afterUpdate(function($row){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['company_model_id'])) {
                model("promotion")
                    ->where(['relevance_model_type'=>'aptitude',"relevance_model_id"=>$row['id']])
                    ->update(['principal_model_id'=>$row['company']['principal_model_id']]);
            }
            if (isset($changeData['business_licence'])) {
                $row->company->save(['business_licence'=>$changeData['business_licence']]);
            }
        });
    }

    public function getNameAttr($value, $data) {
        if (isset($this['company_model_id'])) {
            return $this->company->name;
        }
        return "";
    }


    public function getEnglishNameAttr($value, $data) {
        if (isset($this['company_model_id'])) {
            return $this->company->english_name;
        }
        return "";
    }

    public function getInitPromotionData($species) {
        $data = [];
        if (isset($this['company_model_id'])) {
            $data["principal_model_id"]=$this['company']['principal_model_id'];
        }
        return $data;
    }

    public function company() {
        return $this->hasOne('company','id','company_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function procedure() {
        return $this->hasOne('procedure','id','procedure_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
