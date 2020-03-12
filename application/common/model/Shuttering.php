<?php

namespace app\common\model;

use fast\Random;
use think\Model;

class Shuttering extends Cosmetic
{
    // 表名
    protected $name = 'shuttering';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CO%06d", $maxid);
        });
    }
    public function branch() {
        return $this->hasOne('branch','id','branch_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function procshutter() {
        return $this->hasOne('procshutter','id','procshutter_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function procedure() {
        return $this->hasOne('procedure','id','procedure_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function produce($data, $alternatings) {
        $tempfile =  ROOT_PATH . '/public' . $this['file'];
        $templWord = new \PhpOffice\PhpWord\TemplateProcessor($tempfile);
        foreach($alternatings as $alternating) {
            $field = $alternating['field'];
            if (isset($data[$field['name']])) {
                $val = $data[$field['name']];
                $templWord->setValue(trim($field['title']), $val);
            }
        }

        $suffix = strtolower(pathinfo($tempfile, PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'docx';
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
        ];
        $upload = \think\Config::get('upload');
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);
        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);

        $destFileDir =ROOT_PATH . '/public' . $uploadDir;
        if (!file_exists($destFileDir))
            mkdir($destFileDir);
        $filename = \fast\Random::build("unique").".".$suffix;
        $templWord->saveAs($destFileDir.$filename);

        return $uploadDir.$filename;
    }
}
