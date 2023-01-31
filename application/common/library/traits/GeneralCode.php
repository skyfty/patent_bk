<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 2019/5/20
 * Time: 17:49
 */

namespace app\common\library\traits;

trait GeneralCode
{
    public function generateCode($ids) {
        $code_result = ['code'=>"", 'lines'=>0];
        $rows = $this->model->where("id", "in", $ids)->where("total_lines", "neq", 0)->order("total_lines desc")->select();
        if ($rows === null || count($rows) == 0)
            $this->result($code_result, 1);

        $code_result = $rows[0]->generateCode(3000);
        $this->result($code_result, 1);
    }
}