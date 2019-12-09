<?php

namespace app\common\model;

use EasyWeChat\Foundation\Application;
use think\Db;
use traits\model\SoftDelete;

class Branch extends Cosmetic
{
    use SoftDelete;

    // 表名
    protected $name = 'branch';
    public $keywordsFields = ["name", "idcode"];

    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("MD%06d", $maxid);
        });
    }

    public function amount() {
        //支出
        $where = [
            "reckon_type"=>$this->name,
            "reckon_model_id"=>$this->id
        ];
        $data = [];
        $data['salaryamount'] = Account::where('cheque_model_id',42)->where($where)->sum("money");
        $data['partneramount'] = Account::where('cheque_model_id',45)->where($where)->sum("money");
        $data['payamount'] = Account::hasWhere('cheque',['mold'=>-1])->where($where)->sum("money");
        $data['incomeamount'] = Account::hasWhere('cheque',['mold'=>1])->where($where)->sum("money");
        $data['balance'] = $data['incomeamount'] - $data['payamount'];
        $data['cash'] = $data['balance'];

        $this->isUpdate(true)->allowField(true)->save($data);
    }


    public function courses($start,$end, $staff_id = null) {
        $result = [];
        $classrooms = model("classroom")->where("branch_model_id", $this->id)->field("id,name as title")->cache(true)->select();
        if ($classrooms && count($classrooms) > 0) {
            $periods = model("period")->where("branch_model_id", $this->id)->cache(true)->select();
            $startDate = strtotime($start);
            $endDate = strtotime($end);
            for ($stepscope = $startDate; $stepscope < $endDate;){
                $stepend = strtotime('+1 day', $stepscope);
                $appointTime = date("Y-m-d", $stepscope);
                foreach ($periods as $rk=>$v) {
                    $starttime = strtotime($appointTime ." ".$v->interval_begin);
                    $endtime = strtotime($appointTime ." ".$v->interval_end);
                    foreach ($classrooms as $room) {
                        $list = Calendar::with("course")
                            ->where('course.branch_model_id', $this->id)
                            ->where('course.classroom_model_id', $room->id)
                            ->where(function($query)use($staff_id){
                                if ($staff_id != null) {
                                    $query->where('course.staff_model_id', $staff_id);
                                }
                            })
                            ->where('calendar.starttime', $starttime)->order("id desc")->select();
                        if ($list) {
                            foreach ($list as $ck => $data) {
                                $allDay = ($data['starttime'] === $data['endtime'] && date("H:i:s", $data['starttime']) == '00:00:00');
                                $title = $data->course->promotion->name . ",";
                                $title .= $data->course->promotion->age_text. ",";
                                $title .= $data->course->customer_count. "人";
                                $classroom = $data->course->classroom;
                                $classroom_id = "";
                                if ($classroom) {
                                    $title.="," .   $classroom->name;
                                    $classroom_id = $classroom->id;
                                }
                                $staff = $data->course->staff;
                                if ($staff) {
                                    $title.="," .  $staff->name;
                                }
                                $classname = '';
                                if ($data['endtime'] < time()) {
                                    $classname.=" fc-expired ";
                                }
                                if ($data->course->customer_count >= $data->course->classroom->customer_max) {
                                    $classname.=" fc-full-seats ";
                                }

                                $result[] = [
                                    'id' => $data['id'],
                                    'title' => $title,
                                    'start' => date("c", $data['starttime']),
                                    'end' => date("c", $data['endtime']),
                                    'resourceId' => $classroom_id,
                                    'backgroundColor' => "{$data['background']}",
                                    'borderColor' => "{$data['background']}",
                                    'allDay' => $allDay,
                                    'url' => 'javascript:void(0);',
                                    'className' => $classname,
                                    'classroom_model_id' => $room->id,
                                    'appoint_time' => $appointTime,
                                    'appoint_course' => $v->course,
                                    'period_model_id' => $v->id,
                                    'customer_count' => $data->course->customer_count,
                                ];
                            }
                        } else {
                            $result[] = [
                                'id' => -1,
                                'title' => "没有课程安排",
                                'start' => date("c", $starttime),
                                'end' => date("c", $endtime),
                                'resourceId' => $room->id,
                                'backgroundColor' => "rgb(81, 169, 51)",
                                'borderColor' => "rgb(81, 169, 51)",
                                'allDay' => false,
                                'url' => 'javascript:void(0);',
                                'className' => $endtime < time() ? 'fc-unused fc-expired' : 'fc-unused',
                                'appoint_time' => $appointTime,
                                'appoint_course' => $v->course,
                                'period_model_id' => $v->id,
                                'classroom_model_id' => $room->id,
                                'customer_count' => 0,
                            ];
                        }
                    }
                }
                $stepscope = $stepend;
            }
        }
        return $result;
    }

    public function getWechatAttr($value, $data) {
        return [
            'app_id'=>$data['app_id'],
            'secret'=>$data['secret'],
            'token'=>$data['token'],
            'aes_key'=>$data['aes_key'],
            'authurl'=>$data['authurl'],
            'domain'=>$data['domain'],
            'mch_id'=>isset($data['mch_id'])?$data['mch_id']:"",
            'mch_key'=>isset($data['mch_key'])?$data['mch_key']:"",
            'notify_url'=>isset($data['notify_url'])?$data['notify_url']:"",
            'cert_path'=>isset($data['cert_path'])?$data['cert_path']:"",
            'key_path'=>isset($data['key_path'])?$data['key_path']:"",

        ];
    }

    static public function getWechatApp($branch) {
        $appconfig = $branch && $branch['app_id'] && $branch['authurl']?$branch->wechat: Config::get('wechat');
        $appconfig['debug'] = \think\Config::get('app_debug');
        $appconfig['log'] = [
            'level' => 'debug',
            'file'  => 'easywechat.log',
        ];
        if ($appconfig['mch_id']) {
            $appconfig['payment'] = [
                'mch_id'=>$appconfig['mch_id'],
                'mch_key'=>$appconfig['mch_key'],
                'notify_url'=>$appconfig['notify_url'],
                'cert_path'=>$appconfig['cert_path'],
                'key_path'=>$appconfig['key_path'],
            ];
        }
        return new Application($appconfig);
    }

    public function getSelectField($name, $value) {
        $list= Fields::get(['name'=>$name,'model_table'=>$this->name],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getSignTextAttr($value, $data) {
        $value = $value ? $value : $data['sign'];
        $list= Fields::get(['name'=>'sign','model_table'=>'branch'],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getPaymentTextAttr($value, $data) {
        $value = $value ? $value : $data['payment'];
        $list= Fields::get(['name'=>'payment','model_table'=>'branch'],[],true)->content_list;
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStartTimeTextAttr($value, $data) {
        $value = $value ? $value : $data['starttime'];
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }


    public function getEndTimeTextAttr($value, $data) {
        $value = $value ? $value : $data['endtime'];
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }
}
