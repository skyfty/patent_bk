<?php

namespace app\common\model;

use fast\Random;
use think\Model;

class Customer extends Cosmetic
{
    use \traits\model\SoftDelete;

    // 表名
    protected $name = 'customer';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);

        self::beforeUpdate(function($row){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['provider_ids'])) {
                if ($row['provider_ids'] == '') {
                    $row['provider_amount'] = 0;
                } else {
                    $row['provider_amount'] = count(explode(",", $row['provider_ids']));
                }
            }
        });

        $upstat = function($row){
            $where = [];
            if (isset($row['branch_model_id'])) {
                $where['branch_model_id']=$row->branch_model_id;
            }
            $quantity = self::where($where)->count();

            $where = ["table"=>"customer","field"=>'quantity'];
            if (isset($row['branch_model_id'])) {
                $where['branch_model_id']=$row->branch_model_id;
            }
            $stat = new Statistics();
            if (($ns = $stat->where($where)->find()) == null) {
                $stat->data($where, true);
            } else $stat = $ns;
            $stat->save(['value' => $quantity]);
        };
        self::afterInsert($upstat);self::afterDelete($upstat);

        $updateBranchStat = function($id) {
            $customerIds = self::where("branch_model_id", $id)->column("id");
            $customerIds = array_unique($customerIds);
            model("branch")->where("id", $id)->update([
                'customer_amount' =>count($customerIds),
                'customer_ids'=>implode(",", $customerIds)
            ]);
        };
        $upChangeStat = function($row) use($updateBranchStat) {
            if (isset($row['branch_model_id'])) {
                $updateBranchStat($row['branch_model_id']);
            }
        };
        self::beforeInsert($upChangeStat);self::beforeDelete($upChangeStat);
        self::beforeUpdate(function($row)use($upChangeStat, $updateBranchStat){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['branch_model_id'])) {
                $updateBranchStat($changeData['branch_model_id']);
                if (isset($row->origin['branch_model_id'])) {
                    $updateBranchStat($row->origin['branch_model_id']);
                }
            }
        });


        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("KH%06d", $maxid);
        });


    }


    public function amount() {

    }

    public function genearch() {
        return $this->hasOne('genearch','id','genearch_model_id',[],'LEFT')->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->setEagerlyType(0);
    }

    public function getSexTextAttr($value, $data) {
        $value = $value ? $value : $data['sex'];
        $list= Fields::get(['model_table'=>'customer','name'=>'sex'],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getMembershipTextAttr($value, $data) {
        $value = $value ? $value : $data['membership'];
        $list= Fields::get(['model_table'=>'customer','name'=>'membership'],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }
}

