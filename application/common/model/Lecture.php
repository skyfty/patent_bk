<?php

namespace app\common\model;

use think\Model;

class Lecture extends Cosmetic
{
    // 表名
    protected $name = 'lecture';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'id'
    ];
    public $keywordsFields = ["name", "idcode"];

    use \app\common\library\traits\PrelectureModel;

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("LE%06d", $maxid);
        });
    }
}
