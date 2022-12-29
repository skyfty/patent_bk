<?php

namespace app\admin\controller;
use app\admin\model\Modelx;


class Warehouse extends Cosmetic
{
    use \app\common\library\traits\Cascader;

    /**
     * Package模型对象
     * @var \app\admin\model\Package
     */
    protected $model = null;
    protected $selectpageFields = ['id','name','idcode','pid'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Warehouse;
        $this->dataLimit = false;

    }

    public function del($ids = "") {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $cnt = \app\admin\model\Warehouse::where("pid", $v->id)->count();
                if ($cnt > 0)
                    $this->error(__("%s存在子分类或是内容， 不可以删除",$v->name));

                $count += $v->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    public function index() {
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $modelType = $this->request->param("model_type");
            $relationType = $this->request->param("relation_type");
            $promotionId = $this->request->param("promotion_model_id");

            list($where, $sort, $order, $offset, $limit) = $this->buildparams($this->searchFields,$relationType);

            $subquery = function($query)use($relationType, $promotionId, $modelType){
                $query->table('fa_'.$relationType)->where("promotion_model_id", $promotionId)->field($modelType.'_model_id');
            };
            $this->model->where($where);
            if ($modelType != null) {
                $this->model->where("model_type",$modelType);
                if ($relationType == $modelType) {
                    $this->model->with($modelType)->where('promotion_model_id', $promotionId);
                } else {
                    $this->model->where('model_id','in',$subquery);
                }
            }
            $total = $this->model->count();

            $this->model->where($where);
            if ($modelType != null) {
                $this->model->where("model_type", $modelType);
                if ($relationType == $modelType) {
                    $this->model->with($modelType)->where('promotion_model_id', $promotionId);
                } else {
                    $this->model->where('model_id', 'in', $subquery);
                }
            }
            $this->model->order($sort, $order)->limit($offset, $limit);
            $list = $this->model->select();

            return json(array("total" => $total, "rows" => collection($list)->toArray()));
        }
        $cosmeticModel = Modelx::get(['table' => "warehouse"],[],true);
        if (!$cosmeticModel) {
            $this->error('未找到对应模型');
        }
        $this->assignScenery($cosmeticModel->id, ['index']);
        return $this->view->fetch("index");
    }
}
