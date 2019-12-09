<?php

namespace app\admin\model;

use think\Model;

class UserRule extends \app\common\model\UserRule
{
    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public static function getTreeList($selected = [])
    {
        $ruleList = collection(self::where('status', 'normal')->select())->toArray();
        $nodeList = [];
        foreach ($ruleList as $k => $v)
        {
            $state = array('selected' => $v['ismenu'] ? false : in_array($v['id'], $selected));
            $nodeList[] = array('id' => $v['id'], 'parent' => $v['pid'] ? $v['pid'] : '#', 'text' => __($v['title']), 'type' => 'menu', 'state' => $state);
        }
        return $nodeList;
    }

}
