<?php

namespace app\admin\controller\general;

use app\admin\model\Fields;
use app\common\controller\Backend;

/**
 * 文件管理
 *
 * @icon fa fa-list
 * @remark 用于统一管理网站的所有分类,分类可进行无限级分类
 */
class Distribution extends Backend
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $membership = Fields::get(['model_table'=>'customer','name'=>'membership'])->content_list;
        unset($membership[-1]);
        $this->view->assign("membership", $membership);
        $this->view->assign("distribution", \think\Config::get("distribution"));
        return $this->view->fetch();
    }

    public function edit($ids = NULL) {
        $distribution = $this->request->param("distribution/a");
        file_put_contents(APP_PATH . 'extra' . DS . 'distribution.php', '<?php' . "\n\nreturn " . var_export($distribution, true) . ";");
        $this->success();
    }
}
