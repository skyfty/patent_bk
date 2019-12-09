<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

class Cheque extends \app\common\model\Cheque
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;


    // 追加属性
    protected $append = [
        'mold_text',
        'related_text',
        'reckon_text',
        'inflow_text',
        'inflow_model_id_text',
    ];

    protected static function init()
    {
        $uptable = function ($row) {
            $m = Modelx::get($row['reckon_id'],[],true);
            if ($m) {
                $row['reckon_table'] = $m['table'];
            }
            $m = Modelx::get($row['inflow_id'],[],true);
            if ($m) {
                $row['inflow_table'] = $m['table'];
            }
            $m = Modelx::get($row['related_id'],[],true);
            if ($m) {
                $row['related_table'] = $m['table'];
            }
        };
        self::beforeInsert($uptable); self::beforeUpdate($uptable);
    }
}
