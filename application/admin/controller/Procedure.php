<?php

namespace app\admin\controller;

use app\admin\model\Modelx;
use app\common\controller\Backend;
use think\App;

/**
 * 业务步骤
 *
 * @icon fa fa-circle-o
 */
class Procedure extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Procedure;
    }

    public function index() {
        if ($this->request->isAjax() || !$this->request->has("relevance_model_type")) {
            return parent::index();
        }

        $cosmeticModel = Modelx::get(['table' => "procedure"],[],!App::$debug);
        $this->assignScenery($cosmeticModel->id, ['index']);
        return $this->view->fetch("list");
    }

}
