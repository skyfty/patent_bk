<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

class Expound extends \app\common\model\Expound
{
    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['owners_model_id'] = $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::beforeUpdate(function($row){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['detail'])) {
                $pos = strpos($changeData['detail'],",");
                if ($pos !== false) {
                    $warehouse_model_id = substr($changeData['detail'], 0, $pos);
                    $change = substr($changeData['detail'], $pos + 1);
                    $pos = strpos($change,",");
                    if ($pos !== false) {
                        $changeWarehouse = substr($change, 0, $pos);
                        $changeContent = substr($change, $pos + 1);
                        $params = ['promotion_model_id' => $row['promotion_model_id'], 'exlecture_model_id' => $row['exlecture_model_id']];
                        foreach (['primary', 'second', 'third', 'entire'] as $mv) {
                            $params[$mv] = $row[$mv];
                            if (isset($row[$mv]['warehouse']) && $row[$mv]['warehouse']['id'] == $warehouse_model_id) {
                                $params[$mv][$changeWarehouse]['condition'] = $changeContent;
                            }
                        }
                        $params = formatPresetParams($params);

                        foreach (['primary', 'second', 'third', 'entire','detail'] as $mv) {
                            $row[$mv] = $params[$mv];
                        }
                    }
                }
            }
        });
    }
}
