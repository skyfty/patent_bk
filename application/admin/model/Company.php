<?php

namespace app\admin\model;

use app\admin\library\Auth;
use fast\Random;
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

