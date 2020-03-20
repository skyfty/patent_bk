<?php

namespace app\common\model;

use fast\Random;
use think\Db;
use think\Model;

class Staff extends Cosmetic
{
    use \app\common\library\traits\Gatherer;

    use \traits\model\SoftDelete;

    // 表名
    protected $name = 'staff';
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
                $row['provider_amount'] = count(explode(",", $changeData['provider_ids']));
            }
        });

        $beforeupdate = function($row){
            if (isset($row['quarters'])) {
                $quarters = $row['quarters'];
                if (is_string($quarters)) {
                    $quarters = explode(",", $quarters);
                }

                $groupName = [];
                $quartersKeyword = [];
                foreach(\app\admin\model\AuthGroup::all($quarters) as $v) {
                    if (isset($v['department']) && $v->department) {
                        $departmentName = $v->department->name;
                        $quartersKeyword[] = $v['name']." - ".$departmentName;
                    } else {
                        $quartersKeyword[] = $v['name'];
                    }
                    $groupName[] = $v['name'];
                }
                $row['leading'] = in_array('校区负责人', $groupName)?1:0;
                $row['adviser'] = in_array('客户顾问', $groupName)?1:0;

                $row['quarters_keywords'] = implode("\r\n", $quartersKeyword);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);
    }

    public function admin() {
        return $this->hasOne('admin');
    }

    public function getGroupAttr($value, $data)
    {
        $value = $value ? $value : $data['group_model_id'];
        return $value?explode(",", $value):[];
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function getGroupKeywordsAttr($value)
    {
        return str_replace( "\r\n", "<br/>",$value);
    }
}

