<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

class Preset extends \app\common\model\Preset
{

    protected static function init()
    {

        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::beforeInsert(function($row){
            $weigh = self::withTrashed()->max("weigh") + 1;
            $row['weigh'] = $weigh;

        });
    }

    public function animation() {
        return $this->hasOne('animation','id','animation_id')->setEagerlyType(0);
    }

    use \app\admin\library\traits\PresetModel;
}
