<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 2019/5/20
 * Time: 17:49
 */

namespace app\common\library\traits;

trait Player
{
    public function play($url) {
        $this->view->assign("url", $url);
        return $this->view->fetch();
    }
}