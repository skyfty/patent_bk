<?php

namespace app\common\model;

use think\Model;

class Exlecture extends Cosmetic
{
    // 表名
    protected $name = 'exlecture';

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
            $row['idcode'] = sprintf("EXLE%06d", $maxid);
        });
    }

    public function expounds() {
        return $this->hasMany('expound','exlecture_model_id')->order("weigh desc");
    }


    public function getPhaseTextAttr() {
        $data = $this->getData("phase");
        return $data?str_replace(",", "-", $data):"";
    }
}
