<?php

namespace app\customer\model;

use think\Model;

/**
 * 会员模型
 */
class Promotion Extends \app\common\model\Promotion
{
    // 追加属性
    protected $append = [
        "species"
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $row['creator_model_id'] = 2;
        });
        parent::init();
    }

    public static function getContent($params)
    {
        $field = isset($params['pos']) ? 'pos' : 'pos';
        $value = isset($params[$field]) ? $params[$field] : '';
        $row = self::where("advert_pos", $value)->cache(true)->find();
        $result = '';
        if ($row) {
            if ($row['advertimage']) {
                $result = '<img src="' . $row['advertimage'] . '" class="img-responsive"/>';
            }
            $result = $row['adverturl'] ? '<a href="' . (preg_match("/^https?:\/\/(.*)/i", $row['adverturl']) ? $row['adverturl'] : url($row['advert'])) . '">' . $result . '</a>' : $result;
        }
        return $result;
    }

}
