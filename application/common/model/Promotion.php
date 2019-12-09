<?php

namespace app\common\model;

class Promotion extends Cosmetic
{
    use \traits\model\SoftDelete;

    // 表名
    protected $name = 'promotion';
    public $keywordsFields = ["name", "idcode"];

    public function getPictureListAttr($value, $data)
    {
        $value = $value ? $value : $data['pictures'];
        if ($value) {
            $value = explode(",", $value);
        }
        return $value ? $value : [];
    }


    public function getAgeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['age'];
        if (!is_array($value))$value = explode(",", $value);
        $result = [];
        $list= Fields::get(['model_table'=>'promotion','name'=>'age'],[],true)->content_list;
        foreach($value as $v) {
            $result[] = $list[$v];
        }
        return $result?implode(",",$result):"";
    }

    public function getStageTextAttr($value, $data)
    {
        $value = $value ? $value : $data['stage'];
        if (!is_array($value))$value = explode(",", $value);
        $result = [];
        $list= Fields::get(['model_table'=>'promotion','name'=>'stage'],[],true)->content_list;
        foreach($value as $v) {
            $result[] = $list[$v];
        }
        return $result?implode(",",$result):"";
    }
    public function procedures()
    {
        return $this->hasMany('procedure','promotion_id');
    }

    public function inductions()
    {
        return $this->hasMany('induction','promotion_model_id');
    }

    public function datums()
    {
        return $this->hasMany('datum','promotion_model_id');
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->setEagerlyType(0);
    }

    public function lores()
    {
        return $this->hasMany('lore','promotion_model_id');
    }
    public function genre()
    {
        return $this->hasOne('genre','id','genre_cascader_id')->setEagerlyType(0);
    }
    public function exlectures()
    {
        return $this->hasMany('exlecture','promotion_model_id');
    }

    public function allexlecture()
    {
        $exlectureids = [];
        $ids = model("exlecture")->where('promotion_model_id', $this['id'])->where("pid", 0)->order("weigh asc")->column("id");
        foreach($ids as $exid) {
            $exlectureids[] = $exid;
            model("exlecture")->get($exid)->children($exlectureids);
        }

        $exlectures = [];
        foreach($exlectureids as $id) {
            $exlecture = model("exlecture")->where("id", $id)->where('type', 'lecture')->find();
            if ($exlecture) {
                $exlectures[] = $exlecture;
            }
        }
        return $exlectures;
    }
}
