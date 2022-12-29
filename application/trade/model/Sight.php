<?php

namespace app\trade\model;

use think\Model;

class Sight extends Model
{
    // 表名
    protected $name = 'sight';

    public function fields()
    {
        $bfields = array('name','title','type','content','defaultvalue',
            'rule','msg','ok','tip','decimals','length','minimum','maximum',
            'extend',"content_list","newstatus", "editstatus","relevance","remark"
        );
        return $this->belongsTo('Fields')->setEagerlyType(0)->bind($bfields);
    }

    public function scenery()
    {
        return $this->belongsTo('Scenery')->setEagerlyType(0);
    }
}
