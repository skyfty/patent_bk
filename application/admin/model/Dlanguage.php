<?php

namespace app\admin\model;

use app\admin\library\Auth;
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

    }

    public function updateStatistics() {
        $cnt = model("codesegment")->where("dlanguage_model_id", $this['id'])->count();
        $lines = model("codesegment")->where("dlanguage_model_id", $this['id'])->sum("lines_cnt");
        $this->save(["codesegment_count"=>$cnt,"total_lines"=>$lines]);
    }

}
