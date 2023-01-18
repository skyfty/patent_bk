<?php

namespace app\trade\controller;

use app\admin\library\Auth;
use app\common\controller\Backend;
use app\trade\model\Scenery;
use app\trade\model\Sight;


/**
 * 后台控制器基类
 */
class Trade extends Backend
{
    /**
     * 权限控制类
     * @var Auth
     */
    protected $noNeedLogin = [];


    protected $searchFields = ['name', 'idcode'];

    protected $modelSceneValidate = true;

    protected $relationSearch =[];

    public function _initialize()
    {
      parent::_initialize();

    }

    protected function spectacle($model) {
        return $model;
    }

    protected function getDataLimitAdminIds() {
        return null;

    }

    protected function assignFields($model, $name) {
        $scenery = Scenery::get(['model_table' => $model,'name'=>$name],[],true);
        $fields =  Sight::with('fields')->where(['scenery_id'=>$scenery['id']])->where("fields.name", "not in",[
            "branch","creator","owners",
        ])->order("weigh", "asc")->cache(true)->select();;
        $this->view->assign('fields', $fields);
    }

}
