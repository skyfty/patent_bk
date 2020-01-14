<?php

namespace app\common\model;

use fast\Random;
use think\Model;

class Persion extends Cosmetic
{
    // 表名
    protected $name = 'persion';
    public $keywordsFields = [];

    protected static function init()
    {
        parent::init();
    }

    public function principal()
    {
        return $this->morphOne('Principal', 'substance');
    }
}

