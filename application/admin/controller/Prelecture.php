<?php

namespace app\admin\controller;

/**
 * 授课流程模板
 *
 * @icon fa fa-circle-o
 */
class Prelecture extends Cosmetic
{
    
    /**
     * Prelecture模型对象
     * @var \app\admin\model\Prelecture
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Prelecture;
        $this->dataLimit = false;

    }


    public function enumFeatch($pid, $lecatenates, $templet_model_id) {
        foreach($lecatenates as $k=>$v) {
            $lecture = model("lecture")->get($k);
            $sortlist = \app\admin\model\Prelecture::where("pid", $pid)->field("id,status")->order("weigh asc")->select();

            $data = [
                "pid"=>$pid,
                "type"=>$lecture['type'],
                "name"=>$lecture['name'],
                "status"=>$lecture['status'],
                "lecture_id"=>$lecture['id'],
                "templet_model_id"=>$templet_model_id,
            ];
            $validate = new \app\admin\validate\Prelecture;
            if (!$validate->scene('add')->check($data)) {
                throw new \think\Exception($validate->getError());
            }
            $prelecture = \app\admin\model\Prelecture::create($data);

            for($i = count($sortlist) - 1; $i >= 0; --$i) {
                if ($sortlist[$i]['status'] != "locked") {
                    $newcat = ["id"=>$prelecture['id'],'status'=>$prelecture['status']];
                    array_splice($sortlist, $i, 0, [$newcat]);
                    break;
                }
            }
            $newcnt = count($sortlist);
            for($i = 0; $i < $newcnt; ++$i) {
                $weigh = $i + 1;
                \app\admin\model\Prelecture::where("id", $sortlist[$i]['id'])->update(['weigh'=>$weigh]);
            }
            if ($lecture['type'] == "catenate") {
                $this->enumFeatch($prelecture['id'], $v['children'], $templet_model_id);
            }
            foreach($lecture->coursewares as $courseware) {
                $data = $courseware->getData();
                \app\admin\model\Preset::create([
                    "prelecture_model_id"=>$prelecture['id'],
                    "templet_model_id"=>$templet_model_id,
                    "name"=>$data['name'],
                    "status"=>$data['status'],
                    "primary"=>$data['primary'],
                    "second"=>$data['second'],
                    "third"=>$data['third'],
                    "detail"=>$data['detail'],
                    "entire"=>$data['entire'],
                ]);
            }

        }
    }

    public function add() {
        if (!$this->request->isPost()) {
            $lecture_id = $this->request->param("lecture_id");
            if ($lecture_id) {
                $this->assign("lecture_id", $lecture_id);
            } else {
                $this->assign("lecture_id", 0);
            }
            return parent::add();
        }

        $params = $this->request->post("row/a");
        if (!$params || !$params['lectures']) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $pid = $this->request->param("pid", 0);
        try {
            $lecatenatetree = json_decode($params['lectures'], true);
            if ($lecatenatetree !== false) {
                $db     = $this->model->getQuery();
                $db->startTrans();
                try {
                    $this->enumFeatch($pid, $lecatenatetree, $params['templet_model_id']);
                    $db->commit();
                } catch (\Exception $e) {
                    $db->rollback();
                    throw $e;
                }
                $this->success();
            } else {
                $this->error($this->model->getError());
            }
        } catch (\think\exception\PDOException $e) {
            $this->error($e->getMessage());
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function enumtree($templet_model_id, $id, &$chequelList) {
        $list = $this->model->where('pid', $id)->where('templet_model_id', $templet_model_id)->order("weigh asc")->select();
        foreach ($list as $k => $v) {
            $lecatenateitem = $v->tree_node;
            $lecatenateitem['children'] = [];
            $this->enumtree($templet_model_id, $v['id'], $lecatenateitem['children']);
            $chequelList[] = $lecatenateitem;
        }
    }

    public function classtree($templet_model_id = 0, $id = 0) {
        $chequelList =  [];
        $this->enumtree($templet_model_id, $id, $chequelList);
        return $chequelList;
    }

    public function slideshare($ids) {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    use \app\admin\library\traits\Preweigh;
}
