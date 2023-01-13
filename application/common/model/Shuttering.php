<?php

namespace app\common\model;

use fast\Random;
use think\Loader;
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

    public function catalog()
    {
        return $this->hasOne('catalog','id','catalog_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
    public function species()
    {
        return $this->hasOne('species','id','species_cascader_id')->joinType("LEFT")->setEagerlyType(0);
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
        }elseif($this['type'] == "image") {
            $suffix = $suffix ? $suffix : 'png';
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
        } elseif($this['type'] == "image"){
            $file_field = model("fields")->get($this['file']);
            if (!$file_field) {
                return "";
            }
            if ($file_field['relevance']) {
                $data = $data[$file_field['relevance']];
            }
            return $data[$file_field['name']];
        } else {
            $templWord = new \PhpOffice\PhpWord\TemplateProcessor($tempfile);
            foreach($fields as $field) {
                $relevance = $data;
                if (isset($field['relevance']) && $field['relevance']) {
                    $relevance = $relevance[$field['relevance']];
                }

                if (isset($relevance[$field['name']])) {
                    if ($field['type'] == "model") {
                        $val = $relevance[$field['name']]['name'];
                    } else {
                        $method = 'get' . Loader::parseName($field['name'], 1) . 'AttrText';
                        if (method_exists($relevance, $method)) {
                            $val = $relevance->$method($relevance, $this->data, $this->relation);
                        } else {
                            $val = $relevance[$field['name']];
                            if ($field['type'] == "select") {
                                $content_list = $field->content_list;
                                $val = $content_list[$val];
                            }
                        }
                    }

                    $val=str_replace('&','&amp;',$val);
                    $val=str_replace('<','&lt;',$val);
                    $val=str_replace('>','&gt;',$val);
                    $val=str_replace('\'','&quot;',$val);
                    $val=str_replace('"','&apos;',$val);
                    $val = str_replace([PHP_EOL, "\n"], '<w:br />', $val);
                    $val = substr_replace($val, "<w:t xml:space='preserve'>", 0, 0)."</w:t>";

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
