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

    public function generateCode() {
        $code_result = ['code'=>"", 'lines'=>0];
        $codesegment_ids = model("codesegment")->where("dlanguage_model_id", $this['id'])->column("id");
        if (count($codesegment_ids) == 0) {
            return $code_result;
        }
        $picked_idx = [];
        while($code_result['lines'] < 3000) {
            $rand_id =  $codesegment_ids[rand(0, count($codesegment_ids))];
            if (in_array($rand_id, $picked_idx)) {
                continue;
            }
            $codesegment = model("codesegment")->get($rand_id);
            $code_result['code'] .= $codesegment['code'] . PHP_EOL;
            $code_result['lines'] += $codesegment['lines_cnt'];
        }
        return $code_result;
    }

}
