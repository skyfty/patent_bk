<?php

namespace app\admin\model;

use app\admin\library\Auth;
use fast\Random;
use EasyWeChat\Foundation\Application;
use think\Config;


class Staff extends \app\common\model\Staff
{
    // 追加属性
    protected $append = [
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("ST%06d", $maxid);
            $row['avatar'] = "/assets/img/avatar.png";
        });

        self::afterInsert(function($row){
            $branch = $row->branch;
            $app = new Application($branch && $branch['app_id']? $branch->wechat:Config::get('wechat'));
            $sceneValue = "SID_".$row['id'];
            $result = $app->qrcode->forever($sceneValue);
            if ($result) {
                $qrcodeimg = $app->qrcode->url($result->ticket);
                if ($qrcodeimg) {
                    $row->save(['qrcodeimg' => $qrcodeimg]);
                }
            }
        });

        $beforeupdate = function($row){

        };
        self::beforeInsert($beforeupdate);self::beforeUpdate($beforeupdate);


        $upstat = function($row){
            $quantity = self::where(['branch_model_id'=>$row->branch_model_id])->count();
            $where = ["table"=>"staff","field"=>'quantity', 'branch_model_id'=>$row->branch_model_id];
            $stat = new Statistics();
            if (($ns = $stat->where($where)->find()) == null) {
                $stat->data($where, true);
            } else $stat = $ns;
            $stat->save(['value' => $quantity]);
        };
        self::afterInsert($upstat);

        $updateBranchStat = function($id) {
            $staffIds = self::where("branch_model_id", $id)->column("id");
            Branch::where("id", $id)->update([
                'staff_amount' =>count($staffIds),
                'staff_ids'=>implode(",", $staffIds)
            ]);
        };

        self::beforeUpdate(function($row)use($updateBranchStat){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['branch_model_id'])) {
                $updateBranchStat($changeData['branch_model_id']);
                if (isset($row->origin['branch_model_id'])) {
                    $updateBranchStat($row->origin['branch_model_id']);
                }
            }
        });

        $updateAuthGroupAccess = function($adminId, $row) {
            if ($adminId && $row['quarters']) {
                $dataset = [];
                $quarters = explode(",",$row->getData('quarters'));
                foreach ($quarters as $value) {
                    $dataset[] = ['uid' => $adminId,'group_id' => $value];
                }
                model('AuthGroupAccess')->saveAll($dataset);
            }
        };
        self::afterInsert(function ($row)use($updateAuthGroupAccess) {
            if ($row['admin_name']) {
                $params['username'] = $row['admin_name'];
                $params['nickname'] = $row['name'];
                $params['salt'] = Random::alnum();
                $params['avatar'] = $row['avatar'];
                $params['telephone'] = $row['telephone'];
                $params['idcode'] = $row['idcode'];
                $params['password'] = md5(md5("123456") . $params['salt']);
                $params['qrcodeimg'] = $row['qrcodeimg'];

                $admin = $row->admin()->save($params);
                if ($admin) {
                    $row['admin_id'] = $admin->id;
                    $updateAuthGroupAccess($admin->id, $row);
                    db('staff')->update(['admin_id' => $admin->id,'id'=>$row->id]);
                }
            }

            if (isset($row['group_model_id']) && $row['group_model_id']) {
                $modelGroup = model('ModelGroup')->where("id", "in", $row['group_model_id'])->where("type", 'fixed')->select();
                foreach($modelGroup as $k=>$v) {
                    $members = $v['content'];
                    $members[] = $row['id'];
                    $v->save(["content"=>$members]);
                }
            }
        });

        self::afterDelete(function ($row)use($upstat) {
            $upstat($row);
            $row->admin()->delete($row['admin_id']);
        });

        $updateAuthGroupAccess = function($adminId, $row) {
            if ($adminId && isset($row['quarters']) && $row['quarters']) {
                $dataset = [];
                $quarters = explode(",",$row->getData('quarters'));
                foreach ($quarters as $value) {
                    $dataset[] = ['uid' => $adminId,'group_id' => $value];
                }
                model('AuthGroupAccess')->saveAll($dataset);
            }
        };

        self::afterUpdate(function ($row) use($updateAuthGroupAccess){
            $params = [];
            if (isset($row['avatar'])) {
                $params['avatar'] = $row['avatar'];
            }
            if (isset($row['name'])) {
                $params['nickname'] = $row['name'];
            }
            if (isset($row['telephone'])) {
                $params['telephone'] = $row['telephone'];
            }
            if (isset($row['station_state'])) {
                $params['status'] = ($row['station_state'] == 0?"disabled":"normal");
            }
            if (isset($row['admin_id'])) {
                db('admin')->where('id', $row->admin_id)->update($params);
                model('AuthGroupAccess')->where('uid', $row->admin_id)->delete();
                $updateAuthGroupAccess($row->admin_id, $row);
            }
        });


        $updateBranchStat = function($id) {
            $staffIds = self::where("branch_model_id", $id)->column("id");
            model("branch")->where("id", $id)->update([
                'staff_amount' =>count($staffIds),
                'staff_ids'=>implode(",", $staffIds)
            ]);
        };
        $upChangeStat = function($row) use($updateBranchStat) {
            if (isset($row['branch_model_id']) && $row['branch_model_id']) {
                $updateBranchStat($row['branch_model_id']);
            }
        };
        self::beforeInsert($upChangeStat);self::beforeDelete($upChangeStat);
        self::beforeUpdate(function($row)use($upChangeStat, $updateBranchStat){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['branch_model_id'])) {
                $updateBranchStat($changeData['branch_model_id']);
                if (isset($row->origin['branch_model_id'])) {
                    $updateBranchStat($row->origin['branch_model_id']);
                }
            }
        });
    }

}

