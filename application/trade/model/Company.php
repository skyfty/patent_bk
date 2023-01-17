<?php

namespace app\trade\model;

use app\admin\library\Auth;
use fast\Random;
use think\Exception;
use think\Model;

class Company extends \app\common\model\Company
{
    public $append = [
        'industry'
    ];
    protected static function init()
    {
        parent::init();
    }
}

