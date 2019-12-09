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
    protected $selectpageFields = ['idcode','name', 'id', 'status','class_number'];
    protected $searchFields = ['name', 'idcode','slug'];

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, []);
        parent::_initialize();
        $this->model = model("promotion");
        $this->assignconfig("animations", model("animation")->cache(true)->select());
        $this->assignconfig("expoundColors", \think\Config::get('expound'));

    }

    public function datum() {
        $this->view->assign("datumType", Fields::get(['model_table'=>'datum','name'=>'type'], [], true)->content_list);
        return $this->view();
    }

    public function slideshare($ids) {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        $this->view->assign("exlectures", $row->allexlecture());
        $this->view->assign("row", $row);
        return $this->view->fetch("../../common/view/slideshare");
    }


    public function view() {
        if ($this->request->has("ids") && $this->admin['staff_id']) {
            $branch_model_id = $this->staff->branch_model_id;
            if ($branch_model_id != 0) {
                $distrib = model("distribute")->where('branch_model_id', $branch_model_id)->where('promotion_model_id', $this->request->param("ids"))->find();
                if (!$distrib) {
                    $this->error(__('You have no permission'));
                }
            }
        }
        return parent::view();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)  {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        $branch_model_id = $this->admin['staff_id']?$this->staff->branch_model_id:null;
        if ($branch_model_id != null) {
            if ($row['branch_model_id'] != $branch_model_id) {
                $this->error(__('You have no permission'));
            }
        }
        return parent::edit($ids);
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids) {
            $branch_model_id = $this->admin['staff_id']?$this->staff->branch_model_id:null;
            if ($branch_model_id != null) {
                $ids = explode(",", $ids);
                $aids = $this->model->where("id", 'in', $ids)->where("branch_model_id", $branch_model_id)->column("id");
                $ids = implode(",", array_intersect_assoc($ids, $aids));
                if (!$ids) {
                    $this->error(__('You have no permission'));
                }
            }
        }
        parent::del($ids);
    }

    public function graph() {
        $scope = $this->request->get("scope", '');
        if (!$scope) {
            return [];
        }
        $scope = explode(" - ", $scope);
        $scope[0] = strtotime($scope[0]);$scope[1] = strtotime($scope[1]);

        $data=[];
        $type = $this->request->param("type", "increased");

        $legend = [];
        switch ($type) {
            case "increased":{
                $xAxis = [
                    "type"=>"category",
                    "boundaryGap"=>false,
                ];
                for($stepscope = $scope[0];$stepscope<=$scope[1];) {
                    $stepend = strtotime('+1 day',$stepscope);
                    $xAxis['data'][] = date('m-d',$stepscope);
                    $stepscope = $stepend;
                }
                $data['xAxis'][] = $xAxis;

                $cheque = Genre::all();
                foreach ($cheque as $ck => $cv) {
                    $legend[] = $cv['name'];
                    $series = [
                        "type" => 'line',
                        "name" => $cv['name'],
                        "data" => [],
                    ];
                    for ($stepscope = $scope[0]; $stepscope <= $scope[1];) {
                        $stepend = strtotime('+1 day', $stepscope);
                        $series['data'][] = $this->model->where("createtime", "BETWEEN", [$stepscope, $stepend])->where("genre_cascader_id", $cv['id'])->count();
                        $stepscope = $stepend;
                    }
                    $data['series'][] = $series;
                }
                break;
            }
            case "classamount": {
                $cheque = Genre::all();
                $series = [
                    "type" => 'pie',
                    "name" => "课程类别",
                    "radius" => "55%",
                    "center" => ['50%', '60%'],
                    "data" => [],
                ];
                foreach ($cheque as $ck => $cv) {
                    $legend[] = $cv['name'];
                    $amout = $this->model->where("createtime", "BETWEEN", [$scope[0], $scope[1]])->where("genre_cascader_id", $cv['id'])->count("id");
                    $series['data'][] = ["value"=>$amout, "name"=>$cv['name']];
                }
                $data['series'][] = $series;
                break;
            }
        }
        $data['legend']['data'] = $legend;
        $this->result($data,1);
    }

    public function schedule($ids) {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isAjax()) {
            try {
                $start = $this->request->get('start');
                $end = $this->request->get('end');

                $calendar = model("calendar");
                $calendar->with("provider");
                $calendar->where('starttime', 'between', [strtotime($start), strtotime($end)]);
                $calendar->order("id desc");
                $calendar->group(" provider.appoint_course, provider.classroom_model_id");
                $list = $calendar->select();
                $result = [];
                foreach ($list as $k => $v) {
                    $result[] = $v['render'];
                }
                return json($result);
            } catch (\think\exception\PDOException $e) {
                $this->error($e->getMessage());
            } catch (\think\Exception $e) {
                $this->error($e->getMessage());
            }
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    protected function spectacle($model) {
        $branch_model_id = $this->request->param("branch_model_id");
        if ($branch_model_id == null) {
            if ($this->auth->isSuperAdmin() || !$this->admin || !$this->admin['staff_id']) {
                return $model;
            }
        }
        $branch_model_id = $branch_model_id != null ?$branch_model_id: $this->staff->branch_model_id;

        if ($branch_model_id != 0) {
            $model->where(function($query)use($branch_model_id){
                $query->where("id", "in",function($query)use($branch_model_id){
                    $query->table("__DISTRIBUTE__")->where(function($query){
                        $state = $this->request->param("state", "");
                        if ($state !== "") {
                            $query->where("state", $state);
                        }
                    })->where("branch_model_id", $branch_model_id)->field("promotion_model_id");
                })->whereOr("promotion.branch_model_id", $branch_model_id);
            });
        }

        $package_id = $this->request->param("package_id");
        if ($package_id) {
            $model->where(function($query)use($package_id){
                $query->where("id", "in",function($query)use($package_id){
                    $query->table("__ROPE__")->where("package_model_id", $package_id)->field("promotion_model_id");
                });
            });
        }

        if (isset($this->staff) && !$this->request->has("noauth")) {
            $groupIds = $this->staff['group'];
            $model->where("id", "in",function($query)use($groupIds){
                $query->table("__WARRANT__")->where("group_model_id","in", $groupIds)->field("promotion_model_id");
            });
        }

        return $model;
    }

    public function handbooks() {
        $this->view->assign("rows", $this->model->all());
        return $this->view->fetch();
    }

    public function exclusive() {
        return parent::add();
    }
}
