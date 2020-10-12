<?php

namespace app\admin\model;

use think\Model;

class Sight extends Model
{
    // 表名
    protected $name = 'sight';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
    ];

    protected static function init()
    {
        $afterUpdateCallback = function ($row) {
            $scenery = Scenery::get($row['scenery_id']);
            if ($scenery) {
                $fields = Sight::with('fields')->where('scenery_id', $row['scenery_id'])->order("weigh", 'asc')->column('fields.name');
                $scenery->fields = implode(',', $fields);
                $scenery->save();
            }
        };

        self::beforeInsert(function ($row) {
            $scenery = Scenery::get($row['scenery_id']);
            $row->model_id = $scenery['model_id'];
        });

        self::afterInsert(function ($row) use($afterUpdateCallback) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);

            $afterUpdateCallback($row);
        });
        self::afterDelete($afterUpdateCallback);
    }

    public function fields()
    {
        $bfields = array('name','title','type','content','defaultvalue',
            'rule','msg','ok','tip','decimals','length','minimum','maximum',
            'extend',"content_list","newstatus", "editstatus","relevance","remark"
        );
        return $this->belongsTo('Fields')->setEagerlyType(0)->bind($bfields);
    }

    public function scenery()
    {
        return $this->belongsTo('Scenery')->setEagerlyType(0);
    }
}
