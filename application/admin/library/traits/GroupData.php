<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;

use app\admin\model\AuthGroup;
use fast\Tree;

trait GroupData
{
    public function assignGroupData($branch = null) {

        $this->childrenAdminIds = $this->auth->getChildrenAdminIds(true);
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds(true,$branch);

        Tree::instance()->init(collection(\app\admin\model\AuthGroup::where('id', 'in', $this->childrenGroupIds)->select())->toArray());
        $groupdata = [];
        if ($this->auth->isSuperAdmin()) {
            $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));
            foreach ($result as $k => $v) {
                $groupdata[$v['id']] = $v['name']." - ".$v['department']['name'];
            }
        }
        else {
            $result = [];
            $groups = $this->auth->getGroups();
            foreach ($groups as $m => $n) {
                $childlist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray($n['id']));
                $temp = [];
                foreach ($childlist as $k => $v) {
                    $temp[$v['id']] = $v['name']." - ".$v['department']['name'];;
                }
                $result[__($n['name'])] = $temp;
            }
            $groupdata = $result;
        }
        $this->view->assign('groupdata', $groupdata);
    }
}