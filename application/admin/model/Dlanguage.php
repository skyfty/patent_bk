<?php

namespace app\admin\model;

use app\admin\library\Auth;
use app\common\model\Statistics;
use think\Model;
use traits\model\SoftDelete;

class Dlanguage extends  \app\common\model\Dlanguage
{
    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['owners_model_id'] = $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        $updateLanguageFields = function($row){
            $dlanguages = [];
            foreach (model("dlanguage")->column("id,name") as $k=>$v) {
                $lv = $k."|".$v;
                array_push($dlanguages,$lv);
            }
            $dlanguages = implode("\r\n", $dlanguages);
            model("fields")->get(2376)->save(["content"=>$dlanguages]);
        };
        self::afterInsert($updateLanguageFields);self::afterDelete($updateLanguageFields);

        self::afterDelete(function ($row){
            model("codesegment")->where("dlanguage_model_id", $row['id'])->delete();
        });
    }

    public function updateStatistics() {
        $cnt = model("codesegment")->where("dlanguage_model_id", $this['id'])->count();
        $lines = model("codesegment")->where("dlanguage_model_id", $this['id'])->sum("lines_cnt");
        $this->save(["codesegment_count"=>$cnt,"total_lines"=>$lines]);
    }

}
