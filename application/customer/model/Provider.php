<?php

namespace app\customer\model;

use app\common\model\Fields;

class Provider extends \app\common\model\Provider
{
    // 追加属性
    protected $append = [
        'shared',
    ];
    protected static function init()
    {

        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;
        });
        parent::init();
    }


    public static function getList($list, $tag)
    {
        return $list;
    }

    public function getCampaignListAttr($value, $data) {
        return explode(",", $data['campaign']);;
    }

}
