<?php

namespace app\common\model;

class Paragraph extends Cosmetic
{
    // 表名
    protected $name = 'paragraph';
    public $keywordsFields = ["name", "idcode"];


    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::max("id") + 1;
            $row['idcode'] = sprintf("BL%06d", $maxid);
        });


        $update = function($row) {
            $row['content'] = $row->article->content;
        };
        self::beforeInsert($update);self::beforeUpdate($update);
    }

    public function division() {
        return $this->hasOne('division','id','division_model_id')->joinType("LEFT")->setEagerlyType(0);
    }

    public function getContentTextAttr($value, $data)
    {
       if ($this->article->type == "image") {
            return "<img src='".$data['content']."'/>";
       } else {
           return $data['content'];
       }
    }


    public function article() {
        return $this->hasOne('article','id','article_model_id')->joinType("LEFT")->setEagerlyType(0);
    }
}
