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
        $suffix = strtolower(pathinfo($tempfile, PATHINFO_EXTENSION));
        if ($this['type'] == "excel") {
            $suffix = $suffix ? $suffix : 'xlsx';
        } else {
            $suffix = $suffix ? $suffix : 'docx';
        }
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


        if ($this['type'] == "excel") {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempfile);
            $worksheet = $spreadsheet->getActiveSheet();
            foreach($alternatings as $alternating) {
                $field = $alternating['field'];
                if (isset($data[$field['name']])) {
                    $val = $data[$field['name']];
                    $title = trim($field['title']);
                }
            }
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'xlsx');
            $writer->save($destFileDir.$filename);
        } else {
            $templWord = new \PhpOffice\PhpWord\TemplateProcessor($tempfile);
            foreach($alternatings as $alternating) {
                $field = $alternating['field'];
                if (isset($data[$field['name']])) {
                    $val = $data[$field['name']];
                    $templWord->setValue(trim($field['title']), $val);
                }
            }
            $templWord->setValue("CURRENT_DATE", date("Y年m月d日"));
            $templWord->setValue("CURRENT_TIME", date("H时i分s秒"));
            $templWord->setValue("CURRENT_DATE_TIME", date("Y年m月d日 H时i分s秒"));
            $templWord->setValue("CURRENT_YEAR", date("Y"));
            $templWord->setValue("CURRENT_MONTH", date("m"));
            $templWord->setValue("CURRENT_DAY", date("d"));
            $templWord->setValue("TOMORROW", date("Y年m月d日",strtotime("+1 day")));
            $templWord->setValue("POSTNATAL", date("Y年m月d日",strtotime("+2 days")));

            $templWord->saveAs($destFileDir.$filename);
        }
        return $uploadDir.$filename;
    }
}
