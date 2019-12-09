<?php

namespace app\admin\model;

use think\Db;
use think\Model;
use traits\model\SoftDelete;

class ModelGroup extends \app\common\model\ModelGroup
{
    // 追加属性
    protected $append = [
        'branch'
    ];
    protected static function init()
    {
        parent::init();

        $update = function ($row) {
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['content']) && $row['type'] == "fixed") {
                if ($changeData['content']) {
                    $cc = explode(",", $changeData['content']);
                } else {
                    $cc = [];
                }
                if (isset($row->origin['content'])) {
                    $rc = ($row->origin['content'] != ""?explode(",", $row->origin['content']):[]);
                } else {
                    $rc = [];
                }
                $c1 = array_diff($cc, $rc);
                $c2 = array_diff($rc, $cc);
                $c1 = array_unique(array_merge($c1, $c2));
                if (count($c1) > 0) {
                    $mt = model($row['model_type'])->field("id")->where("id", "in", $c1)->select();
                    foreach($mt as $v) {
                        $kks = [];
                        $ids = [];
                        $groups = self::where($v['id'],"exp","FIND_IN_SET(".$v['id'].",content)")->field("title,id,branch_model_id")->where("type", "fixed")->select();
                        foreach($groups as $v2) {
                            $kks[] = $v2->visible(['title'])->toJson();;
                            $ids[] = $v2['id'];
                        }
                        $data = ["group_model_keyword"=>json_encode($kks), "group_model_id"=>implode(",", $ids)];
                        db($row['model_type'])->where("id", $v['id'])->update($data);
                    }
                }
            }
        };
        self::afterUpdate($update);
    }

    public function setContentAttr($value, $data)
    {
        if (is_array($value) && count($value) > 0) {
            if (isset($value[0]['condition'])) {
                $value = json_encode($value);
            } else {
                $value = implode(",", $value);
            }
        }
        return $value;
    }
}
