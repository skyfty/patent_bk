<?php

namespace app\admin\controller;
ini_set("error_reporting","E_ALL & ~E_NOTICE");

use app\admin\model\Modelx;
use app\admin\model\Sight;
use app\common\controller\Backend;
use PclZip;
use think\App;
use think\Log;

/**
 * 资质管理
 *
 * @icon fa fa-circle-o
 */
class Aptitude extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Aptitude;
    }

    use \app\admin\library\traits\Produce;
    use \app\common\library\traits\Produce;
    use \app\common\library\traits\Procshutter;

    public function progress() {
        $ids =$this->request->param("ids", null);
        if ($ids === null)
            $this->error(__('Params error!'));

        $cosmeticModel = Modelx::get(['table' => $this->model->raw_name],[],!App::$debug);
        if (!$cosmeticModel) {
            $this->error('未找到对应模型');
        }

        $row = $this->getModelRow($ids);
        $plans = model("plan")->where("model", $this->model->raw_name)->order("order asc")->select();
        foreach($plans as $plan) {
            switch ($plan['id']) {
                case 1: { //关注服务号
                    $plan['status'] = "light";
                    break;
                }
                case 2: { //注册主体信息
                    if ($row['company_model_id']) {
                        $plan['status'] = "light";
                    }
                    break;
                }
                case 3: { //发起知识产权贯标服务

                    break;
                }
                case 4: { //填写必要信息

                    break;
                }
                case 5: { //生成全部文档

                    break;
                }
                case 6: { //操作打印签字

                    break;
                }
                case 7: { //生成全套资料包

                    break;
                }
                case 8: { //评审

                    break;
                }
                case 9: { //上传证书

                    break;
                }
                case 10: { //查询评审结果

                    break;
                }
                case 11: { //结算

                    break;
                }
            }
        }
        $this->view->assign("plans", $plans);
        $this->view->assign("row", $row);

        $content = $this->view->fetch();
        return array("content"=>$content, "fields"=>[]);

    }



    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        $model->where("aptitude.branch_model_id", $branch_model_id);

        return $model;
    }
}
