<?php

namespace app\common\model;

use think\Model;

class Copyright extends Professional
{
    // 表名
    protected $name = 'copyright';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CO%06d", $maxid);
        });

        self::afterDelete(function($row){
            unlink($row['code']);
        });
    }

    public function getInitPromotionData($species) {
        $data = [];
        if (isset($this['company_model_id'])) {
            $data["principal_model_id"]=$this['company']['principal_model_id'];
        }
        return $data;
    }

    public function company() {
        return $this->hasOne('company','id','company_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function procedure() {
        return $this->hasOne('procedure','id','procedure_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function runningSoftware() {
        return $this->hasOne('osystem','id','running_software_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function developOs() {
        return $this->hasOne('osystem','id','develop_os_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function developTool() {
        return $this->hasOne('dtool','id','develop_tool_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function auxiliarySoftware() {
        return $this->hasOne('osystem','id','auxiliary_software_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function getPublishAttrText() {
        $data = $this->getData();
        if ($data['publish'] == 0) {
            return "未发表";
        } else {
            return "发表时间：".$this['publish_date'].PHP_EOL."发表地点：".$this['publish_address'];
        }
    }

    public function getCodeAttr() {
        $code = "";
        $data = $this->getData();
        if ($data['code'] != "" && file_exists($data['code'])) {
            $code = file_get_contents($data['code']);
        }
        return $code;
    }


    public function getLanguageAttrText() {
        $dlanguages = model("dlanguage")->where("id", "in", $this['language'])->column("name");
        return implode(",", $dlanguages);
    }

    public static function saveCodeFile($code) {
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
        ];
        $destFileDir =ROOT_PATH . '/public/uploads/' . str_replace(array_keys($replaceArr), array_values($replaceArr), "{year}{mon}{day}");
        if (!file_exists($destFileDir))
            mkdir($destFileDir);
        $fileName = \fast\Random::build("unique");
        $destFileName = $destFileDir."/".$fileName;
        file_put_contents($destFileName, $code);
        $lines = count(explode("\n", $code));
        return ['code'=>$destFileName, 'lines'=>$lines];
    }

    public function saveCode($code) {
        $result = Copyright::saveCodeFile($code);
        return $this->save($result);
    }

}
