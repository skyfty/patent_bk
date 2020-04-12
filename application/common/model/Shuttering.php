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

    public function produce($data, $fields) {
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

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'xlsx');
            $writer->save($destFileDir.$filename);
        } else {
            $tmpfname = tempnam(sys_get_temp_dir(), "shutter");
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter(\PhpOffice\PhpWord\IOFactory::load($tempfile), 'HTML');
            $objWriter->save($tmpfname);
            $content = file_get_contents($tmpfname);
            unlink($tmpfname);

            foreach($fields as $field) {
                $relevance = $data;
                if (isset($field['relevance']) && $field['relevance']) {
                    $relevance = $relevance[$field['relevance']];
                }
                if (isset($relevance[$field['name']])) {
                    $val =  str_replace(PHP_EOL, '<br />', $relevance[$field['name']]);
                    $searchval = '${'.trim($field['title']).'}';
                    $content = str_replace($searchval, $val, $content);
                }
            }
            $content = str_replace($searchval, $val, $content);
            $content = str_replace("CURRENT_DATE", date("Y年m月d日"), $content);
            $content = str_replace("CURRENT_TIME", date("H时i分s秒"), $content);
            $content = str_replace("CURRENT_DATE_TIME", date("Y年m月d日 H时i分s秒"), $content);
            $content = str_replace("CURRENT_YEAR", date("Y"), $content);
            $content = str_replace("CURRENT_MONTH", date("m"), $content);
            $content = str_replace("CURRENT_DAY", date("d"), $content);
            $content = str_replace("TOMORROW", date("Y年m月d日",strtotime("+1 day")), $content);
            $content = str_replace("POSTNATAL", date("Y年m月d日",strtotime("+2 days")), $content);

            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            \PhpOffice\PhpWord\Shared\Html::addHtml($phpWord->addSection(), $content, true);
            $phpWord->save($destFileDir.$filename);
        }
        return $uploadDir.$filename;
    }
}
