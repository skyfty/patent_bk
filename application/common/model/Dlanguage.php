<?php

namespace app\common\model;

use think\Model;

class Dlanguage extends  Cosmetic
{
    // 表名
    protected $name = 'dlanguage';

    public $keywordsFields = [];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("CL%06d", $maxid);
        });


    }

    public function generateCode($cnt) {
        $code_result = ['code'=>"", 'lines'=>0];
        $codesegment_ids = model("codesegment")->where("dlanguage_model_id", $this['id'])->column("id");
        if (count($codesegment_ids) != 0) {
            shuffle($codesegment_ids);
            foreach ($codesegment_ids as $id) {
                $codesegment = model("codesegment")->get($id);
                $code_result['code'] .= $codesegment['code'] . PHP_EOL;
                $code_result['lines'] += $codesegment['lines_cnt'];
                if ($code_result['lines'] > $cnt) {
                    break;
                }
            }
        }
        return $code_result;
    }
}
