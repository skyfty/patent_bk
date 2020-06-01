<?php

namespace app\common\model;

use think\Model;

class Templet extends Cosmetic
{
    use \traits\model\SoftDelete;

    // 表名
    protected $name = 'templet';

    // 表名
    public $keywordsFields = ["name", "idcode"];
}
