<?php

namespace app\common\model;

use EasyWeChat\Foundation\Application;
use think\Db;
use traits\model\SoftDelete;

class Branch extends Cosmetic
{
    use SoftDelete;

    // 表名
    protected $name = 'branch';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("MD%06d", $maxid);
        });
    }

    public function amount() {
        //支出
        $where = [
            "reckon_type"=>$this->name,
            "reckon_model_id"=>$this->id
        ];
        $data = [];
        $data['salaryamount'] = Account::where('cheque_model_id',42)->where($where)->sum("money");
        $data['partneramount'] = Account::where('cheque_model_id',45)->where($where)->sum("money");
        $data['payamount'] = Account::hasWhere('cheque',['mold'=>-1])->where($where)->sum("money");
        $data['incomeamount'] = Account::hasWhere('cheque',['mold'=>1])->where($where)->sum("money");
        $data['balance'] = $data['incomeamount'] - $data['payamount'];
        $data['cash'] = $data['balance'];

        $this->isUpdate(true)->allowField(true)->save($data);
    }


    public function getSelectField($name, $value) {
        $list= Fields::get(['name'=>$name,'model_table'=>$this->name],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getSignTextAttr($value, $data) {
        $value = $value ? $value : $data['sign'];
        $list= Fields::get(['name'=>'sign','model_table'=>'branch'],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getPaymentTextAttr($value, $data) {
        $value = $value ? $value : $data['payment'];
        $list= Fields::get(['name'=>'payment','model_table'=>'branch'],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStartTimeTextAttr($value, $data) {
        $value = $value ? $value : $data['starttime'];
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }


    public function getEndTimeTextAttr($value, $data) {
        $value = $value ? $value : $data['endtime'];
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }
}
