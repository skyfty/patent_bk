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
        $row = $this->model->get($ids);
        if ($row === null)
            $this->error(__('Params error!'));

        $code = $row->generateCode();
        $this->result($code, 1);
    }
}