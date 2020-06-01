<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\common\library\traits;

use fast\Tree;

trait PrelectureModel
{
    public function children(&$chequelList, $id=null) {
        $id = $id?$id:$this->getData("id");
        $ids = self::where('pid', $id)->order("weigh asc")->column("id");
        foreach ($ids as  $cid) {
            $chequelList[] =$cid;
            $this->children($chequelList, $cid);
        }
    }
}