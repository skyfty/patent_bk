<?php

namespace app\common\model;
use app\admin\library\Auth;

use think\Model;
use traits\model\SoftDelete;

class Policy extends  Cosmetic
{
    // 表名
    protected $name = 'policy';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        $beforeupdate = function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("PO%06d", $maxid);
        });
    }
}

