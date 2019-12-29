<?php

namespace app\common\model;

use think\Model;

class Principalclass extends Cosmetic
{
    // 表名
    protected $name = 'principalclass';

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
            $row['idcode'] = sprintf("PR%06d", $maxid);
        });

        $updateprincipalclass = function($row){
            $data = [];
            foreach(self::all() as $r) {
                $data[] = $r['id']."|".$r['name'];
            }
            $content = implode("\r\n", $data);
            model("fields")->where("name","principalclass")->where("model_table","policy")->setField("content", $content);

        };
        self::afterInsert($updateprincipalclass); self::afterDelete($updateprincipalclass);
    }
}
