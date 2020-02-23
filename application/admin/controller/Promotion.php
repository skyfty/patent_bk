<?php

namespace app\admin\controller;

use app\admin\model\Fields;
use app\admin\model\Modelx;
use app\common\model\Genre;


/**
 * 服务项目
 *
 * @icon fa fa-circle-o
 */
class Promotion extends Cosmetic
{
    
    /**
     * Promotion模型对象
     * @var \app\admin\model\Promotion
     */
    protected $model = null;
    protected $selectpageFields = ['idcode','name', 'id', 'status'];
    protected $searchFields = ['name', 'idcode','slug'];

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, []);
        parent::_initialize();
        $this->model = model("promotion");

    }


    public function add() {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if (!$params) {
                $this->error(__('Parameter %s can not be empty', ''));
            }
            $species_cascader_keyword = json_decode($params['species_cascader_keyword']);
            $params['relevance_model_type'] = $species_cascader_keyword->row->model;

            $db = $this->model->getQuery();
            $db->startTrans();
            try {
                $result = $this->model->validate("promotion.add")->allowField(true)->save($params);
                if ($result !== false) {
                    $db->commit();
                    $this->success("", null, $this->model->get($this->model->id)->toArray());
                } else {
                    $db->rollback();
                    $this->error($this->model->getError());
                }
            } catch (\think\Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
            }
        }
        return parent::add();
    }
}
