<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 2018/12/3
 * Time: 20:52
 */
namespace app\common\library\traits;
use app\common\library\Sms as Smslib;

trait Addressfield
{

    public function getAddressAttr($value, $data)
    {
        $value = $value ? $value : $data['address'];
        if ($value) {
            $value = str_replace("/", " ", $value);
        }
        return $value;
    }

    public function setAddressAttr($value)
    {
        if ($value) {
            $value = str_replace(" ", "/", $value);
        }
        return $value;
    }
}