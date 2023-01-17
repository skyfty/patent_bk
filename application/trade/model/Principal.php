<?php

namespace app\trade\model;
use app\admin\library\Auth;

use PhpOffice\PhpSpreadsheet\Calculation\Exception;
use think\App;
use think\Loader;
use think\Model;
use traits\model\SoftDelete;

class Principal extends  \app\common\model\Principal
{
    // 追加属性
    public $append = [
        "company",
        "persion"
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['branch_model_id'] = 0;
            $row['owners_model_id'] = $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }
}
