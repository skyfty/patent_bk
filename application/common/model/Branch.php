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


    public function getWechatAttr($value, $data) {
        return [
            'app_id'=>$data['app_id'],
            'secret'=>$data['secret'],
            'token'=>$data['token'],
            'aes_key'=>$data['aes_key'],
            'authurl'=>$data['authurl'],
            'domain'=>$data['domain'],
            'mch_id'=>isset($data['mch_id'])?$data['mch_id']:"",
            'mch_key'=>isset($data['mch_key'])?$data['mch_key']:"",
            'notify_url'=>isset($data['notify_url'])?$data['notify_url']:"",
            'cert_path'=>isset($data['cert_path'])?$data['cert_path']:"",
            'key_path'=>isset($data['key_path'])?$data['key_path']:"",

        ];
    }

    static public function getWechatApp($branch) {
        $appconfig = $branch && $branch['app_id'] && $branch['authurl']?$branch->wechat: Config::get('wechat');
        $appconfig['debug'] = \think\Config::get('app_debug');
        $appconfig['log'] = [
            'level' => 'debug',
            'file'  => 'easywechat.log',
        ];
        if ($appconfig['mch_id']) {
            $appconfig['payment'] = [
                'mch_id'=>$appconfig['mch_id'],
                'mch_key'=>$appconfig['mch_key'],
                'notify_url'=>$appconfig['notify_url'],
                'cert_path'=>$appconfig['cert_path'],
                'key_path'=>$appconfig['key_path'],
            ];
        }
        return new Application($appconfig);
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
