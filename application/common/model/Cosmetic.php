<?php

namespace app\common\model;

use think\exception\ClassNotFoundException;
use think\Model;
use traits\model\SoftDelete;

class Cosmetic extends Model
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    protected $dateFormat = 'Y-m-d H:i:s';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $type = [];
    protected $insert = ['creator_model_id'];

    protected function initialize()
    {
        parent::initialize();
        $fields = model("fields")
            ->where("model_table", $this->name)
            ->where("type", "in",['datetime','date','time'] )
            ->where("name", "not in",['createtime','updatetime','deletetime'])->field("name,type")->cache(true)->select();
        foreach($fields as $v) {
            $fmt = $this->dateFormat;
            if ($v['type'] == 'date') {
                $fmt = 'Y-m-d';
            }elseif ($v['type'] == 'time'){
                $fmt = 'H:i:s';
            }
            $this->type[$v['name']] = 'timestamp:'.$fmt;
        }
    }

    protected function writeTransform($value, $type) {
        if (!is_array($type) && strpos($type, ':')) {
            list($type, $param) = explode(':', $type, 2);
            if ($type == 'timestamp' && !$value) {
                $value = null;
            }
        }
        return parent::writeTransform($value, $type);
    }

    public function getRawNameAttr() {
        return $this->name;
    }

    protected static function init()
    {
        self::beforeWrite(function ($row) {
            foreach ($row->getData() as $k => $value) {
                if (is_array($value) && isset($value['field'])) {
                    $value = json_encode(Config::getArrayData($value), JSON_UNESCAPED_UNICODE);
                } else {
                    $value = is_array($value) ? implode(',', $value) : $value;
                }

                $row[$k] = $value;
            }
        });

        $prepareModelField = function($row){
            $fields = Modelx::get(array("table"=>$row->name),[], true)->fields()->where(array("type"=>"model"))->select();
            foreach($fields as $v) {
                $fd = $v->getData();
                $fieldName = $fd['name'];
                $modelId = $fieldName . "_model_id";
                if (isset($row[$modelId]) && $fd['defaultvalue']) {
                    $modelName = $fd['defaultvalue'];
                    $modelPaths = [];
                    foreach(explode(".", $modelName) as $v) {
                        $modelPaths[] = ucfirst($v);
                    }
                    $modelName = implode("", $modelPaths);
                    if ($fd['content'] != "") {
                        $content = json_decode($fd['content'], true);
                        if ($content) {
                            $fd['content'] = $content;
                            if (isset($content['model'])) {
                                $modelName =  $content['model'];
                            }
                        }
                    }
                    $rmodel = model($modelName);

                    if ($rmodel) {
                        $modelIds = explode(",", $row[$modelId]);
                        $rmodel->where("id",'in',$modelIds);

                        $keywordFields = ['name','idcode'];
                        if (isset($rmodel->keywordsFields)) {
                            $keywordFields = array_unique(array_merge($rmodel->keywordsFields, $keywordFields));
                        }
                        if (count($keywordFields) > 0) {
                            $rmodel->append($keywordFields);
                        }

                        $data = $rmodel->select();
                        if ($data) {
                            $kks = [];
                            foreach($data as $v2) {
                                $v2->visible($keywordFields);
                                $kks[] = $v2->toJson();;
                            }
                            $row[$fieldName."_model_keyword"] = json_encode($kks, JSON_UNESCAPED_UNICODE);;
                        }else {
                            $row[$fieldName."_model_keyword"] = "";
                        }
                    }
                }
            }

            $fields = Modelx::get(array("table"=>$row->name),[], true)->fields()->where(array("type"=>"location"))->select();
            foreach($fields as $v) {
                $fd = $v->getData();
                $fieldName = $fd['name'];
                if (isset($row[$fieldName])) {
                    $locationData = explode(",", $row[$fieldName]);
                    if (count($locationData) == 3) {
                        $row[$fieldName . "_lat"] = $locationData[1];;
                        $row[$fieldName . "_lng"] = $locationData[2];;
                    }
                }
            }
        };
        self::beforeInsert($prepareModelField);self::beforeUpdate($prepareModelField);

        $recordLog = function($row) {
            if ($row->origin && count($row->origin) > 0 && isset($row['id'])) {
                $content = [];
                $changeData = $row->readonly("updatetime")->getChangedData();
                foreach($changeData as $k=>$v) {
                    if (isset($row->origin[$k])) {
                        $content[$k] = array($row->origin[$k], $v);
                    }
                }
                ModelLog::setContent($content);
                ModelLog::setModel($row->name, $row->id);
                ModelLog::record(__(request()->action()), 'active', 'locked');
            }
        };
        self::afterUpdate($recordLog);

        self::afterInsert(function($row) {
            $content = [];
            $changeData = $row->readonly("updatetime")->getChangedData();
            foreach($changeData as $k=>$v) {
                if (isset($row->origin[$k])) {
                    $content[$k] = array($row->origin[$k], $v);
                }
            }
            ModelLog::setContent($content);
            ModelLog::setModel($row->name, $row->id);
            ModelLog::record(__(request()->action()), 'active', 'locked');
        });
    }
    public function getStatusList()
    {
        return ['normal' => __('Normal'),'hidden' => __('Hidden'),'locked' => __('Locked'),'light' => __('Light')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function logs()
    {
        return $this->morphMany('ModelLog', 'model');
    }

}
