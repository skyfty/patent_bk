<?php

namespace app\admin\model;

use app\admin\library\Alter;
use app\common\model\Config;
use think\Model;
use think\Db;

class Fields extends \app\common\model\Fields
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'newstatus_text',
        'editstatus_text',
        'content_list',
        'rule_text',
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $model = Modelx::get($row['model_id']);
            if ($model) {
                if ($row['relevance'] != '' && $row['relevance'] != $model['table']) {
                    $relevanceModel = Modelx::get(array("table"=>$row['relevance']));
                    if ($relevanceModel) {
                        $field = Fields::get(array('model_id'=>$relevanceModel['id'],'name'=>$row['name']));
                        if ($field) {
                            $row['type'] = $field['type'];
                            $row['content'] = $field['content'];
                            $row['defaultvalue'] = $field['defaultvalue'];
                            $row['decimals'] = $field['decimals'];
                            $row['length'] = $field['length'];
                            $row['newstatus'] = $row['editstatus'] = "disabled";
                        }
                    }
                }
                $row['model_table'] = $model['table'];
            }
        });

        self::afterInsert(function ($row) {
            $model = Modelx::get($row['model_id']);
            if ($model) {

                if ($row['relevance'] == '') {
                    $alter = Alter::instance()->setTable($model['table'])->setComment($row['title']);
                    if (isset($row['content'])) {
                        $alter->setContent($row['content']);
                    }
                    if (isset($row['decimals'])) {
                        $alter->setDecimals($row['decimals']);
                    } else {
                        $alter->setDecimals(0);
                    }
                    if ($row['type'] == 'model') {
                        $alter->setName($row['name'] . "_model_id");
                        if ($row['content']) {
                            $alter->setLength(255);
                        } else {
                            $alter->setLength(10);
                        }
                        $alter->setType("number");
                        $alter->setDefaultvalue('NULL');
                    }elseif($row['type'] == 'switcher'){
                        $alter->setType("number");
                        $alter->setLength(1);
                        $alter->setName($row['name']);
                        $alter->setDefaultvalue($row['defaultvalue'] == 0?$row['defaultvalue']:1);
                    } elseif($row['type'] == 'cascader'){
                        $alter->setName($row['name'] . "_cascader_id");
                        if ($row['content']) {
                            $alter->setLength(255);
                        } else {
                            $alter->setLength(10);
                        }
                        $alter->setType("number");
                        $alter->setDefaultvalue('NULL');
                    }else {
                        $alter->setName($row['name']);
                        $alter->setLength($row['length']);
                        $alter->setType($row['type']);
                        if (in_array($row['type'],['datetime','date','time'])) {
                            $alter->setDefaultvalue('NULL');
                        } else {
                            $alter->setDefaultvalue($row['defaultvalue']);
                        }
                    }
                    db()->query($alter->getAddSql());

                    if($row['type'] == 'location') {
                        $alter->setType("number");
                        $alter->setDecimals(6);
                        $alter->setLength(20);
                        $alter->setName($row['name'] . "_lat");
                        $alter->setDefaultvalue(0);
                        db()->query($alter->getAddSql());
                        $alter->setName($row['name'] . "_lng");
                        $alter->setDecimals(6);
                        $alter->setLength(20);
                        $alter->setDefaultvalue(0);
                        db()->query($alter->getAddSql());
                    }
                }
                $secenery = request()->param("secenery");
                if ($secenery) {
                    $sightdatas = array();
                    foreach (explode(",", $secenery) as $v) {
                        $sightdatas []= array("scenery_id"=>$v,"fields_id"=>$row["id"],"model_id"=>$row['model_id']);
                    }
                    (new Sight)->saveAll($sightdatas);
                }

                $fields = Fields::where(array('model_id'=>$model['id'],'name'=>array("not in", array("weigh"))))->field('name')->column('name');
                $model->fields = implode(',', $fields);
                $model->save();
            }
        });

        self::afterUpdate(function ($row) {
            $model = Modelx::get($row['model_id']);
            if ($model) {
                if ($row['relevance'] == '') {
                    $alter = Alter::instance();
                    if (isset($row['oldname']) && $row['oldname'] != $row['name']) {
                        $alter->setOldname($row['oldname']);
                    }

                    $alter->setTable($model['table'])->setComment($row['title']);
                    if ($row['content']) {
                        $alter->setContent($row['content']);
                    } else {
                        $alter->setContent("");
                    }
                    if ($row['decimals']) {
                        $alter->setDecimals($row['decimals']);
                    } else {
                        $alter->setDecimals(0);
                    }
                    if ($row['type'] == 'model') {
                        $alter->setName($row['name'] . "_model_id");
                        if ($row['content']) {
                            $alter->setLength(255);
                        } else {
                            $alter->setLength(10);
                        }
                        $alter->setType("number");
                        $alter->setDefaultvalue(0);
                    }elseif ($row['type'] == 'cascader') {
                        $alter->setName($row['name'] . "_cascader_id");
                        if ($row['content']) {
                            $alter->setLength(255);
                        } else {
                            $alter->setLength(10);
                        }
                        $alter->setType("number");
                        $alter->setDefaultvalue(0);
                    }elseif($row['type'] == 'switcher'){
                        $alter->setName($row['name']);
                        $alter->setLength(1);
                        $alter->setType("number");
                        $alter->setDefaultvalue($row['defaultvalue'] == 0?$row['defaultvalue']:1);
                    } else {
                        $alter->setName($row['name']);
                        $alter->setLength($row['length']);
                        $alter->setType($row['type']);
                        if (in_array($row['type'],['datetime','date','time'])) {
                            $alter->setDefaultvalue(null);
                        } else {
                            $alter->setDefaultvalue($row['defaultvalue']);
                        }
                    }
                    db()->query($alter->getModifySql());
                    Fields::where(["relevance"=>$model['table'],"name"=>$row['name']])->update(['content' => $row['content']]);
                }
                $fields = Fields::where('model_id', $model['id'])->field('name')->column('name');
                $model->fields = implode(',', $fields);
                $model->save();
            }

        });

        self::afterDelete(function ($row) {
            $model = Modelx::get($row['model_id']);
            if ($row['relevance'] == '') {
                if ($model) {
                    if ($row['type'] == 'model') {
                        $sql = Alter::instance()->setTable($model['table'])->setName($row['name'] . "_model_id")->getDropSql();
                        db()->query($sql);
                    }elseif ($row['type'] == 'cascader') {
                        $sql = Alter::instance()->setTable($model['table'])->setName($row['name'] . "_cascader_id")->getDropSql();
                        db()->query($sql);
                    }elseif($row['type'] == 'location'){
                        $sql = Alter::instance()->setTable($model['table'])->setName($row['name'] . "_lat")->getDropSql();
                        db()->query($sql);
                        $sql = Alter::instance()->setTable($model['table'])->setName($row['name'] . "_lng")->getDropSql();
                        db()->query($sql);
                        $sql = Alter::instance()->setTable($model['table'])->setName($row['name'])->getDropSql();
                        db()->query($sql);
                    } else {
                        $sql = Alter::instance()->setTable($model['table'])->setName($row['name'])->getDropSql();
                        db()->query($sql);
                    }
                }
            }
            Sight::destroy(array("fields_id"=>$row['id']));
        });


        self::afterDelete(function ($row) {
          if ($row['alternating'] ==1) {
              model("alternating")->where("field_model_id", $row['id'])->delete();
          }
        });

        self::afterUpdate(function ($row) {
            if ($row['alternating'] ==1) {
                $changeData = $row->readonly("updatetime")->getChangedData();
                if (isset($changeData['title'])) {
                    model("alternating")->where("field_model_id", $row['id'])->update([
                        "name"=>$row['title']
                    ]);
                }
            }
        });
    }


    public function getRuleTextAttr($value, $data)
    {
        $value = explode(",", $data['rule']);
        $regexList = [];
        foreach (Config::getRegexList() as $k => $v) {
            if (in_array($k, $value)) {
                $regexList[] = $v;
            }
        }
        return implode(",", $regexList);
    }

    public function getNewstatusTextAttr($value, $data) {
        $value = $value ? $value : (isset($data['status'])?$data['status']:"normal");
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getEditstatusTextAttr($value, $data) {
        return $this->getNewstatusTextAttr($value, $data);
    }

}
