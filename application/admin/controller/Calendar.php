<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\CalendarEvent;
use app\admin\model\Fields;
use app\common\controller\Backend;
use fast\Auth;
use think\exception\PDOException;

/**
 * 日历管理
 *
 * @icon calendar
 */
class Calendar extends Cosmetic
{

    /**
     * Calendar模型对象
     */
    protected $model = null;
    protected $childrenAdminIds = [];
    protected $relationSearch=['course'];

    public function _initialize()
    {
        $this->noNeedRight = array_merge($this->noNeedRight, ["rooms"]);
        parent::_initialize();
        $this->model = model('calendar');
        $this->request->filter(['strip_tags']);
    }

    public function rooms($branch_model_id) {
        $classrooms = model("classroom")->where("branch_model_id", $branch_model_id)->field("id,name as title")->cache(true)->select();
        return $classrooms?$classrooms:[];
    }

    public function index() {
        if (!$this->request->isAjax()) {
            $this->view->assign('branchs', model("branch")->cache(true)->where("status",'neq', "hidden")->select());
            return $this->view->fetch();
        }
        $start = $this->request->get('start');
        $end = $this->request->get('end');
        $branch_model_id = $this->request->get('branch_model_id');
        if ($branch_model_id) {
            $result = model("branch")->get($branch_model_id)->courses($start, $end);
        } else {
            $ids = $this->request->get('ids');
            if (!$ids) {
                $this->error(__('Parameter %s can not be empty', ''));
            }
            $result = $this->model->with("course")->where("calendar.id", "in", explode(",",$ids))->select();
        }
        return json($result);
    }
}
