<?php

namespace app\admin\model;

use think\Model;

class Scenery extends Model
{
    // 表名
    protected $name = 'scenery';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'status_text',
        'pos_text',
        'type_text',
    ];

    protected static function init()
    {
        self::afterDelete(function ($row) {
            Sight::destroy(array("scenery_id"=>$row['id']));
            \app\admin\model\AuthRule::destroy(array("name"=>$row->model['table']."/".$row['name']));
        });

        self::beforeInsert(function ($row) {
            $model = Modelx::get($row['model_id']);
            $row->model_table = $model['table'];
        });

        self::afterInsert(function ($row) {
            //创建时自动添加权重值
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);

            $table = $row->model['table'];
            $model = \app\admin\model\AuthRule::get(array("name"=>$table));
            if ($model) {
                $param = array(
                    "type"=>"file",
                    "pid"=>$model['id'],
                    "title"=>$row['title'],
                    "ismenu"=>0,
                    "status"=>"normal",
                    "icon"=>"fa fa-list",
                );
                if (in_array($row['pos'], ['index', 'view'])){
                    $posscenery = \app\admin\model\AuthRule::get(array("name"=>$table."/".$row['pos']));
                    if ($posscenery) {
                        $param['pid'] = $posscenery['id'];
                    }
                    $param['name'] = $table."/".$row['pos']."/".$row['name'];
                } else {
                    $param['name'] = $table."/".$row['name'];
                }
                \app\admin\model\AuthRule::create($param);
            }
        });
    }

    public function getPosTextAttr($value, $data)
    {
        $value = $value ? $value : $data['pos'];
        $list = $this->getPosList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getPosList()
    {
        return ['index' => __('Index'), 'view' => __('View'), 'block' => __('Block'), 'hinder' => __('Hinder'),'other' => '其他'];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getTypeList()
    {
        return ['default' => __('default'), 'url' => "模块"];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden'), 'locked' => __('Locked')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function model()
    {
        return $this->belongsTo('Modelx', 'model_id', 'id');
    }
    public function sight()
    {
        return $this->hasMany('Sight');
    }
}
