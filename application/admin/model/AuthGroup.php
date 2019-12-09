<?php

namespace app\admin\model;

use app\admin\model\Scenery;
use fast\Tree;
use think\Model;

class AuthGroup extends \app\common\model\AuthGroup
{
    // 追加属性
    protected $append = [
        'department',
        'status_text'
    ];
    protected static function init()
    {
        parent::init();

        self::afterDelete(function ($row) {
            $scenery = Scenery::get($row['scenery_id']);
            $row->model_id = $scenery['model_id'];
        });
    }

    public function department()
    {
        return $this->belongsTo('department', 'auth_department_id')->setEagerlyType(0);
    }


    public function migrate($branchId) {
        $department = static::create([
            "pid"=>$this['pid'],
            "auth_department_id"=>$this['auth_department_id'],
            "name"=>$this['name'],
            "rules"=>$this['rules'],
            "branch_id"=>$branchId,
        ]);
        $groups = static::where("inherit", 1)->where("pid",$this['id'])->select();
        foreach($groups as $g) {
            static::create([
                "pid"=>$department->id,
                "auth_department_id"=>$g['auth_department_id'],
                "name"=>$g['name'],
                "rules"=>$g['rules'],
                "branch_id"=>$branchId,
            ]);
        }
    }
}
