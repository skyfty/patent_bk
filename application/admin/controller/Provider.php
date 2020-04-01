<?php

namespace app\admin\controller;

use app\admin\model\Course;
use app\admin\model\Fields;
use app\admin\model\Modelx;
use app\admin\model\Scenery;
use app\admin\model\Sight;
use app\admin\model\Statistics;
use app\admin\model\TException;
use EasyWeChat\Foundation\Application;
use think\Config;
use think\Exception;
use think\Hook;
use think\Db;
use think\Loader;
use Endroid\QrCode\QrCode;

/**
 * 服务订单
 *
 * @icon fa fa-circle-o
 */
class Provider extends Cosmetic
{
    protected $model = null;
    protected $selectpageFields = [
        'name', 'idcode', 'id', 'state',
        'branch_model_id',
        'customer_model_id',
    ];
    protected $selectpageShowFields = ['idcode'];

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, []);
        parent::_initialize();
        $this->model = model("provider");
    }


    public function add() {
        if (!$this->request->isPost()) {
            $this->assignconfig('provider', Config::get("provider"));
            return parent::add();
        }
        $params = $this->request->post("row/a");
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $db = $this->model->getQuery();
        $db->startTrans();
        try {
            if ($params["promotion_model_id"]) {
                unset($params["species_cascader_id"]);
                $result = $this->model->validate("provider.add")->allowField(true)->save($params);
            } else {
                $species = model("species")->get($params["species_cascader_id"]);
                $result = model($species['model'])->create([
                    "branch_model_id"=>$params["branch_model_id"],
                ]);
                if ($result) {
                    model("provider")->where("promotion_model_id", $result['promotion_model_id'])->update(["staff_model_id"=>$params["staff_model_id"]]);
                }
            }

            if ($result !== false) {
                $db->commit();
                Hook::listen('newprovider',$this->model);
                $this->success("", null, $this->model->visible([],true)->toArray());
            } else {
                $this->error($this->model->getError());
            }
        } catch (\think\Exception $e) {
            $db->rollback();
            $this->error($e->getMessage());
        }
    }


    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if (!$branch_model_id) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("provider.branch_model_id", $branch_model_id);

        return $model;
    }
}
