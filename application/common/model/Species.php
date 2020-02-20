<?php

namespace app\common\model;

class Species extends Cosmetic
{
    // 表名
    protected $name = 'species';
    public $keywordsFields = ["name", "idcode"];
}
