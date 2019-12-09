<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\admin\model\Warrant;
use app\common\controller\Backend;
use EasyWeChat\Foundation\Application;
use think\Config;
use think\Db;
use think\Hook;
use think\Validate;

/**
 * 后台首页
 * @internal
 */
class Index extends Backend
{

    protected $noNeedLogin = ['login','manage','ggg','player','layercontrol'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 后台首页
     */
    public function index()
    {
        //左侧菜单
        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar([
            'dashboard' => '',
        ], $this->view->site['fixedpage']);
        $action = $this->request->request('action');
        if ($this->request->isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }
        $this->view->assign('menulist', $menulist);
        $this->view->assign('navlist', $navlist);
        $this->view->assign('fixedmenu', $fixedmenu);
        $this->view->assign('referermenu', $referermenu);
        $this->view->assign('title', __('Home'));
        return $this->view->fetch();
    }

    /**
     * 管理员登录
     */
    public function login()
    {
        $url = $this->request->get('url', 'index/index');
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $branch = $this->request->post('branch');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'require|token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
            if (Config::get('fastadmin.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }
            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, $branch, $keeplogin ? 86400 : 0);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }
        $background = Config::get('fastadmin.login_background');
        $background = stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background;
        $this->view->assign('background', $background);
        $this->view->assign('title', __('Login'));
        $this->view->assign('branchs', model("branch")->cache(true)->where("status",'neq', "hidden")->column("id,name"));
        Hook::listen("admin_login_init", $this->request);
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        $this->success(__('Logout successful'), 'index/login');
    }

    public function player()
    {
        $template = "layercontrol/player";
        return $this->view->fetch($template);
    }


    public function layercontrol()
    {
        $template = "layercontrol/".$this->request->param("lc");
        return $this->view->fetch($template);
    }

    public function manage()
    {
        \app\admin\model\Provider::where(['state'=>['in', '1,4,5']])->whereTime('starttime', 'd')->chunk(10, function($providers){
            foreach($providers as $v) {
                $v->dispatch();
            }
        });

        $cutime = time();
        if ($cutime > strtotime("19:00:00") && $cutime < strtotime("19:59:59")) {
            \app\admin\model\Provider::where(['state'=>['in', '5']])->whereTime('starttime', 'd')->chunk(10, function($providers){
                foreach($providers as $v) {
                    $v->expire();
                }
            });
        }

        \app\admin\model\Course::where(['status'=>['in', ['unknown','unstarted']]])->whereTime('starttime', 'd')->chunk(10, function($courses){
            foreach($courses as $v) {
                $v->dispatch();
            }
        });


        \app\admin\model\BuyingOpen::where(['status'=>'locked'])->whereTime('endtime', 'd')->chunk(100, function($buyings){
            foreach($buyings as $v) {
                $v->dispatch();
            }
        });


        \app\admin\model\BuyingCommodity::where(['status'=>'running'])->whereTime('endtime', 'd')->chunk(100, function($buyings){
            foreach($buyings as $v) {
                $v->dispatch();
            }
        });
    }

    public function gg()
    {
        $row = model("trade")->get(1);
        $row->refund();

    }
    public function test()
    {
        foreach([
//                    "arrange",
//                    "assembly",
//                    "behavior",
//                    "branch",
//                    "business",
//                    "checkwork",
//                    "classroom",
                    "course",
                    "courseware",
                    "customer",
                    "datum",
                    "equipment",
//                    "exlecture",
//                    "expound",
//                    "genearch",
                  //  "grade",
//                    "induction",
//                    "knowledge",
//                    "largess",
//                    "leave",
//                    "lecture",
//                    "lessons",
//                    "lore",
//                    "material",
//                    "middleware",
//                    "package",
//                    "pattern",
//                    "prelecture",
//                    "presell",
//                    "preset",
//                    "proar",
//                    "procedure",
//                    "proficient",
//                    "promotion",
//                    "provider",
//                    "repertory",
//                    "rope",
//                    "scholarship",
//                    "sellotape",
//                    "staff",
//                    "stuff",
//                    "templet",
//                    "warehouse",
//                    "warrant",
//                    "wisdom",

                ] as $m) {
            $rows = model($m)->select();
            $cnt = count($rows);
            foreach($rows as $row) {
                echo $cnt--."\r\n";
                $admin = model("admin")->get($row['creator_model_id']);
                if ($admin) {
                    $kks[] = $admin->visible(['nickname'])->toJson();;
                    $row["creator_model_keyword"] = json_encode($kks, JSON_UNESCAPED_UNICODE);
                    $row->save();
                }

            }
        }

//        model("account")->where("reckon_type", "branch")->where("reckon_model_id", 0)->save(["branch_model_keyword"=>$kks, "branch_model_id"=>2]);
//        model("account")->where("inflow_type", "branch")->where("inflow_model_id", 0)->save(["branch_model_keyword"=>$kks, "branch_model_id"=>2]);
    }
    public function ggg2()
    {

        $genearchs = model("genearch")->select();
        $cnt1 = count($genearchs);
        foreach ($genearchs as $genearch) {
            echo $cnt1-- . "\r\n";

            $customer_model_ids = model("claim")->where("genearch_model_id", $genearch->id)->column("customer_model_id");
            model("genearch")->where("id", $genearch->id)->update([
                'customer_ids'=>implode(",", $customer_model_ids)
            ]);
        }


//        $genres = model("genre")->where("id", "in",[2,12,11])->select();
//        $cnt3 = count($genres);
//        foreach ($genres as $genre) {
//            echo "  " . $cnt3-- . "\r\n";
//            $lorerange = model("lorerange")->where("id", "in",[11,12])->select();
//
//            $cnt2 = count($lorerange);
//            foreach ($lorerange as $lore) {
//                echo "    " . $cnt2-- . "\r\n";
//                model("LoreGenre.AmountBracket")->enumLoreGenre($genre, $lore['id']);
//            }
//        }
        
    }
    public function ggg()
    {
        $rows = Db::table("fa_promotion_distribute")->select();
        $cnt = count($rows);
        foreach ($rows as $row) {
            echo $cnt-- . "\r\n";
            model("distribute")->create([
                "promotion_model_id"=>$row['promotion_model_id'],
                "branch_model_id"=>$row['branch_model_id'],

            ]);

        }
//        $customers = model("customer")->select();
//        $cnt1 = count($customers);
//        foreach ($customers as $customer) {
//            echo $cnt1-- . "\r\n";
//            $genres = model("genre")->select();
//            $cnt3 = count($genres);
//            foreach ($genres as $genre) {
//                echo "  " . $cnt3-- . "\r\n";
//                $lorerange = model("lorerange")->select();
//
//                $cnt2 = count($lorerange);
//                foreach ($lorerange as $lore) {
//                    echo "    " . $cnt2-- . "\r\n";
//                    model("LoreGenre.CustomerAmountBracket")->enumLoreGenre($genre, $lore['id'],$customer['id']);
//                }
//            }
//        }
//
//
//        $genres = model("genre")->where("id", "in",[2,12,11])->select();
//        $cnt3 = count($genres);
//        foreach ($genres as $genre) {
//            echo "  " . $cnt3-- . "\r\n";
//            $lorerange = model("lorerange")->where("id", "in",[11,12,5,4,3,14,13,6])->select();
//
//            $cnt2 = count($lorerange);
//            foreach ($lorerange as $lore) {
//                echo "    " . $cnt2-- . "\r\n";
//                model("LoreGenre.AmountBracket")->enumLoreGenre($genre, $lore['id']);
//            }
//        }



//        $rows = model("course")->select();
//        $cnt = count($rows);
//        foreach($rows as $row) {
//            echo $cnt--."   ".$row['id']."\r\n";
//
//            $keywordFields = ['name','idcode'];
//
//            $data2 = [];
//            foreach(["period",'branch','classroom','staff','package'] as $m2) {
//                $data = model($m2)->where("id",'in',[$row[$m2.'_model_id']])->select();
//                if ($data) {
//                    $kks = [];
//                    foreach($data as $v2) {
//                        $v2->visible($keywordFields);
//                        $kks[] = $v2->toJson();;
//                    }
//                    $data2[$m2.'_model_keyword'] = json_encode($kks, JSON_UNESCAPED_UNICODE);;
//                }
//
//            }
//
//            $data = model("promotion")->where("id",'in',[$row['appoint_promotion_model_id']])->select();
//            if ($data) {
//
//                $kks = [];
//                foreach($data as $v2) {
//                    $v2->visible($keywordFields);
//                    $kks[] = $v2->toJson();;
//                }
//
//                $data2['appoint_promotion_model_keyword'] = json_encode($kks, JSON_UNESCAPED_UNICODE);;
//
//            }
//            $staff = $row->staff;
//            if ($staff) {
//                $data2['emolument'] = $row->staff->emolument;
////            $data2['createtime'] = $data2['updatetime'] = $row['starttime'];
//
//                model("course")->where("id", $row['id'])->update($data2);
//
//            }

            //$row->save($data2);
            //var_dump($data2);


 //       }
    }
}
