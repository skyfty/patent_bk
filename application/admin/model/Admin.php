<?php

namespace app\admin\model;

use think\Model;
use think\Session;

class Admin extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    public $keywordsFields = ["username","nickname"];
    protected $append = [
        'branch_text',
        'branch',
        'quarters_text',

    ];

    protected static function init()
    {
        parent::init();

        self::afterUpdate(function ($row){
            if (isset($row['staff_id'])) {
                if (isset($row['avatar'])) {
                    $params['avatar'] = $row['avatar'];
                }
                if (isset($row['telephone'])) {
                    $params['telephone'] = $row['telephone'];
                }
                $params['name'] = $row['nickname'];
                db('staff')->where('id', $row->staff_id)->update($params);
            }
        });
    }

    public function resetPassword($uid, $NewPassword)
    {
        $passwd = $this->encryptPassword($NewPassword);
        $ret = $this->where(['id' => $uid])->update(['password' => $passwd]);
        return $ret;
    }

    // 密码加密
    protected function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }

    public function staff() {
        return $this->hasOne('staff', 'id', 'staff_id', [], 'LEFT')->setEagerlyType(0);
    }

    public function getBranchTextAttr($value) {
        $staff = $this->staff;
        return $staff ? $staff->branch->name : "";
    }
    public function getBranchAttr($value) {
        $staff = $this->staff;
        return $staff ? $staff->branch_model_id : 0;
    }
    public function getQuartersTextAttr($value) {
        $staff = $this->staff;
        if (!$staff || $staff->quarters_keywords == "")
            return "管理员";
        $quarters_keywords= explode("\r\n", $staff->quarters_keywords);
        if ($quarters_keywords == null || count($quarters_keywords) == 0)
            return "管理员";
        return explode(" - ", $quarters_keywords[0])[0];
    }
}
