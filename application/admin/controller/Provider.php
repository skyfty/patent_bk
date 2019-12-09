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
        'appoint_promotion_model_id'
    ];
    protected $selectpageShowFields = ['idcode'];

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, ["evaluateurl"]);
        parent::_initialize();
        $this->model = model("provider");
        $this->relationSearch = array_merge($this->relationSearch, ["promotion"]);
    }

    protected function selectIds($promotionId, $sequence) {
        $classNumberField = "genre.order*100+promotion.class_number";
        $promotion = \app\admin\model\Promotion::with("genre")->field([$classNumberField." as class_number","genre.pid"])->where("promotion.id", $promotionId)->find();
        $promotions = \app\admin\model\Promotion::with("genre")->field($classNumberField." as class_number")
            ->order("class_number asc")
            ->where("genre.pid",$promotion->genre->pid)
            ->where($classNumberField.'>'.$promotion->class_number)
            ->limit(0, $sequence)
            ->select();
        return $promotions;
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
            $sequence = 0; $period = 7;;
            if (isset($params['sequence']) && $params['sequence']) {
                $tmpsequence = explode(",", $params['sequence']);
                $pacont = count($tmpsequence);
                if ($pacont > 2 || (!is_numeric($tmpsequence[0]) || ($pacont == 2 && !is_numeric($tmpsequence[1])))) {
                    $this->error(__('Parameter is wrong'));
                }
                unset($params['sequence']);
                $sequence = intval($tmpsequence[0]);
                if ($pacont == 2) {
                    $period = intval($tmpsequence[1]);
                    if ($period <=1) {
                        $this->error(__('Parameter is wrong'));
                    }
                }
            }

            $result = $this->model->validate("provider.add")->allowField(true)->save($params);
            if ($result !== false) {
                if ($sequence > 0) {
                    $promotions = $this->selectIds($params['appoint_promotion_model_id'], $sequence);
                    if ($promotions && count($promotions) > 0) {
                        unset($params['__token__']);
                        $params['appoint_time'] = strtotime($params['appoint_time']);
                        $data = [];
                        foreach($promotions as $promotion) {
                            $params['appoint_time'] = strtotime("+".$period." days", $params['appoint_time']);
                            $params['appoint_promotion_model_id'] = $promotion['id'];
                            $data[] = $params;
                        }

                        $batch = new \app\admin\model\Provider;
                        $result = $batch->allowField(true)->validate("provider.batch")->saveAll($data);
                        if ($result === false) {
                            throw new \think\Exception($batch->getError());
                        }
                    }
                }
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

    public function evaluate($ids) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        $this->view->assign("row", $row);

        $scenery = Scenery::get(['model_table' => "provider", 'name' => 'evaluate', 'pos' => "view"],[],true);
        $where = [
            'scenery_id' => $scenery['id'],
            "fields.name" => ["not in", ["weigh"]]
        ];
        $scenery['fields'] = Sight::with('fields')->where($where)->cache(true)->order("weigh", "asc")->select();
        $this->assignconfig('scenery', $scenery);
        return $this->view->fetch();

    }

    public function listevaluate() {
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $course = model("course")->get($this->request->param("course_model_id"));
            $provider_model_ids = explode(",",$course['provider_model_ids']);
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $this->model->alias("provider")->where($where)->with($this->relationSearch)->where("provider.id", "in", $provider_model_ids);
            $this->spectacle($this->model);
            $total = $this->model->count();

            $this->model->alias("provider")->where($where)->where("provider.id", "in", $provider_model_ids)->with($this->relationSearch)->order($sort, $order)->limit($offset, $limit);
            $this->spectacle($this->model);
            $list = $this->model->select();
            return json(array("total" => $total, "rows" => collection($list)->toArray()));
        }

        $scenery = Scenery::get(['model_table' => "provider", 'name' => 'listevaluate', 'pos' => "other"],[],true);
        $where = [
            'scenery_id' => $scenery['id'],
            "fields.name" => ["not in", ["weigh"]]
        ];
        $scenery['fields'] = Sight::with('fields')->where($where)->cache(true)->order("weigh", "asc")->select();
        $this->assignconfig('scenery', $scenery);
        return $this->view->fetch("listevaluate");
    }

    public function changestaff($ids) {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if (!$params) {
                $this->error(__('Parameter %s can not be empty', ''));
            }
            $rows = $this->model->where("id", "in", $ids)->select();
            if (!$rows){
                $this->error(__('No Results were found'));
            }
            $succcnt = 0;
            foreach($rows as $row) {
                if ($row['state'] == 1) {
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $succcnt++;
                    }
                }
            }
            $this->success("成功修改了" .$succcnt."个订单");
        } else {
            $scenery = Scenery::get(['model_table' => "provider", 'name' => 'view', 'pos' => "view"],[],true);
            $where = [
                'scenery_id' => $scenery['id'],
                "fields.name" => ['in',['staff','branch']]
            ];
            $scenery['fields'] = Sight::with('fields')->where($where)->cache(true)->order("weigh", "asc")->select();
            $this->assignconfig('scenery', $scenery);
            $this->view->assign("ids", $ids);
            return $this->view->fetch();
        }
    }

    public function changeappointtime($ids) {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if (!$params) {
                $this->error(__('Parameter %s can not be empty', ''));
            }
            $rows = $this->model->where("id", "in", $ids)->select();
            if (!$rows){
                $this->error(__('No Results were found'));
            }
            $succcnt = 0;
            foreach($rows as $row) {
                if ($row['state'] == 1) {
                    $number = $params['direct'] * $params['number'];
                    $appoint_time = strtotime($number." weeks", $row->getData('appoint_time'));
                    $cc = date("Y-m-d", $appoint_time);
                    $result = $row->allowField(true)->save(['appoint_time'=>$appoint_time]);
                    if ($result !== false) {
                        $succcnt++;
                    }
                }
            }
            $this->success("成功修改了" .$succcnt."个订单");
        } else {
            $this->view->assign("ids", $ids);
            return $this->view->fetch();
        }
    }

    public function statistic() {
        $stat = Statistics::where(function($query){
            $table = $this->request->param("table");
            if (!$table) {
                $table = strtolower($this->model->raw_name);
            }
            $query->where("table", $table);

            $fields = $this->request->param("field/a");
            if ($fields) {
                $query->where("field", "in", $fields);
            }

        })->column('sum(value),field','field');

        $stepscope = strtotime(date('Y-m-d',time()));
        $stepend = strtotime('+1 day',$stepscope);
        $stat['today']['value'] = $this->model->where("createtime", "BETWEEN", [$stepscope, $stepend])->count();

        $this->result($stat, 1);
    }

    public function ableclassroom() {
        $date = $this->request->param("date");
        if (!$date)
            $this->error(__('No Results were found'));
        $branch_model_id = $this->request->param("branch_model_id");

        $date = strtotime($date);
        $stepbegin = strtotime(date('Y-m-1',$date));
        $stepend = strtotime('+1 month',$stepbegin);
        $classrooms = model("classroom")->where("branch_model_id", $branch_model_id)->cache(true)->column("id,name,idcode,customer_max");
        $result =Course::all([
            'appoint_time'=>['between', [$stepbegin, $stepend]],
            'classroom_model_id'=>['in', array_keys($classrooms)]
        ]);

        $periods = model("period")->where("branch_model_id", $branch_model_id)->cache(true)->select();
        $course = [];
        foreach($periods as $v) {
            $course[] = $v['interval_begin'];
        }
        $this->success('', null, ['classroom'=>$classrooms,'course'=>$course,'appoint'=>$result,]);
    }

    public function signin($ids = null) {
        if (!$ids) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $list = $this->model->where("id", 'in', $ids)->select();
        try {
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->signin();
            }
            $this->success();

        } catch (TException $e) {
            $this->error($e->getMessage());
        }
    }

    public function accomplish($ids = null) {
        if (!$ids) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $list = $this->model->where("id", 'in', $ids)->select();
        try {
            foreach ($list as $k => $v) {
                Db::startTrans();
                try {
                    if ($v->accomplish()) {
                        Hook::listen('accomplish',$v);
                    }
                    Db::commit();
                    $v->countLores();
                } catch (\Exception $e) {
                    Db::rollback();
                    throw $e;
                }
            }
            $this->success();
        } catch (TException $e) {
            $this->error($e->getMessage());
        }
    }

    public function recover($ids = null) {
        if (!$ids) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $list = $this->model->where("id", 'in', $ids)->select();
        try {
            $count = 0;
            foreach ($list as $k => $v) {
                Db::startTrans();
                try {
                    $count += $v->recover();
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    throw $e;
                }
            }
            $this->success();
        } catch (TException $e) {
            $this->error($e->getMessage());
        }
    }

    public function leave($ids = null) {
        if (!$ids) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $list = $this->model->where("id", 'in', $ids)->select();
        try {
            foreach ($list as $k => $v) {
                if ($v->leave()) {
                    Hook::listen('leave',$v);
                }
            }
            $this->success();
        } catch (TException $e) {
            $this->error($e->getMessage());
        }
    }

    public function qrcode($ids) {
        $row = $this->model->with($this->relationSearch)->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        $qrurl = $row->qrcode();
        $this->view->assign("url", $qrurl[1]);
        $this->view->assign("qrurl", $qrurl[0]);
        return $this->view->fetch();
    }

    public function reproduce($ids = null) {
        if (!$ids) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $list = $this->model->where("id", 'in', $ids)->select();
        if (count($list) == 0) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $customer_model_ids = $this->request->param("customer_model_ids");
            if ($customer_model_ids) {
                $customers = model("customer")->where("id", "in", $customer_model_ids)->select();
                Db::startTrans();
                foreach($customers as $customer) {
                    foreach ($list as $k => $v) {
                        $data = [
                            "branch_model_id"=>$customer['branch_model_id'],
                            "customer_model_id"=>$customer['id'],
                            "appoint_time"=>$v['appoint_time'],
                            "appoint_promotion_model_id"=>$v['appoint_promotion_model_id'],
                            "package_model_id"=>$v['package_model_id'],
                            "staff_model_id"=>$v['staff_model_id'],
                            "classroom_model_id"=>$v['classroom_model_id'],
                            "period_model_id"=>$v['period_model_id'],
                            "appoint_course"=>$v['appoint_course'],
                        ];
                        $result = Loader::validate('Provider')->scene('reproduce')->check($data);
                        if ($result !== true) {
                            Db::rollback();
                            throw new Exception($result);
                        }

                        try {
                            $result = \app\admin\model\Provider::create($data);
                            if ($result === false) {
                                throw new Exception($this->model->getError());
                            }
                        } catch (\Exception $e) {
                            Db::rollback();
                            throw $e;
                        }
                    }
                }
                Db::commit();
            }
            $this->success();
        } else {
            $this->view->assign("rows", $list);
            $this->view->assign("row", $list[0]);
            return $this->view->fetch();
        }
    }

    public function calendar($ids = null) {
        if ($this->request->has("start")) {
            $result = [];
            $start = $this->request->get('start');
            $end = $this->request->get('end');
            $list = Calendar::with("provider")->where('calendar.starttime', 'between', [strtotime($start), strtotime($end)])->order("id desc")->select();
            foreach ($list as $k => $v) {
                $r =  $v['render'];
                $r['resourceId'] = $v->provider->classroom_model_id;
                $r['title'] = $v->provider->promotion->name;

                $result[] =$r;
            }

            return json($result);
        } else {
            $content = $this->view->fetch("calendar");
            return array("content"=>$content, "classrooms"=>model("classroom")->all());
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


    public function graph() {
        $data=[];

        $type = $this->request->param("type", "increased");
        $scope = $this->request->get("scope", '');
        if (!$scope) {
            return [];
        }
        $scope = explode(" - ", $scope);
        $scope[0] = strtotime($scope[0]);$scope[1] = strtotime($scope[1]);

        $xAxis = [
            "type"=>"category",
            "boundaryGap"=>false,
        ];
        for($stepscope = $scope[0];$stepscope<=$scope[1];) {
            $stepend = strtotime('+1 day',$stepscope);
            $xAxis['data'][] = date('m-d',$stepscope)."(".date2week($stepscope).")";
            $stepscope = $stepend;
        }
        $data['xAxis'][] = $xAxis;
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();

        $legend = [];
        $cheque = [
            [
                'name'=>'授课订单',
                'where'=>[]
            ],
            [
                'name'=>'已完成未结课',
                'where'=>[
                    'provider.state'=>['in',['5']]
                ]

            ],
            [
                'name'=>'已完成已结课',
                'where'=>[
                    'provider.state'=>['in',['6']]
                ]

            ]
        ];
        foreach($cheque as $ck=>$cv) {
            $legend[] = $cv['name'];
            $series=[
                "type"=>'line',
                "name"=>$cv['name'],
                "data"=>[],
            ];
            for($stepscope = $scope[0]; $stepscope<=$scope[1];) {
                $stepend = strtotime('+1 day',$stepscope);
                $this->model->alias("provider")->where("provider.starttime", "BETWEEN", [$stepscope, $stepend])->where($where)->with($this->relationSearch);
                $this->spectacle($this->model);
                $series['data'][] = $this->model->where($cv['where'])->count();
                $stepscope = $stepend;
            }
            $data['series'][] = $series;
        }
        $data['legend']['data'] = $legend;

        $this->result($data,1);
    }
}
