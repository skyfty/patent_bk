<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;

use app\admin\model\Account;
use app\common\model\Config;

trait Adjective
{
    public function formatAdjective($adjective) {
        $preadjectives = json_decode($this['adjective'],true);
        $details = is_string($adjective['detail'])?explode(",", $adjective['detail']):$adjective['detail'];
        $adjectives = [];
        foreach($details as  $v) {
            if ($v == "self") {
                if (isset($this['body']) && $this['body'] ==1) {
                    $adjectives[] = ['type'=>"warehouse",'data'=>$this->warehouse->name];
                } else {
                    $mname = strtolower($this->name);
                    $adjectives[] = ['type'=>$mname,'data'=>$this['name']];
                }
            } else {
                $pos = strpos($v, "_");
                $field = substr($v, 0, $pos);
                $value = substr($v, $pos + 1);
                if (isset($preadjectives[$field])) {
                    if ($preadjectives[$field]['type'] == "select") {
                        $value = $preadjectives[$field]["content"][$value];
                    }
                    if (isset($preadjectives[$field]['unit'])) {
                        $value.=$preadjectives[$field]['unit'];
                    }
                    $adjectives[] = ['type'=>'adjective','data'=>$value];
                }elseif ($field == "amount") {
                    $ware = $this->warehouse->model;
                    if ($ware) {
                        $value.=$ware->unit_text;
                    }
                    $adjectives[] = ['type'=>'amount','data'=>$value];
                }
            }
        }
        return $adjectives;
    }
}