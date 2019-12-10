<?php

namespace app\admin\model;

class Genre extends \app\common\model\Genre
{
    protected static function init()
    {
        parent::init();

        self::afterInsert(function($row){
            $row->updateChildrens();
        });

        self::afterDelete(function($row){
            $row->updateChildrens();
        });
    }

    public function updateChildrens() {
        static $tree;
        if (!$tree) {
            $tree = \fast\Tree::instance();
            $tree->init(collection(self::field('id,pid,name')->select())->toArray(), 'pid');
        }
        $prow = self::get($this['pid']);
        if ($prow) {
            $childids = $tree->getChildrenIds($this['pid']);
            $prow->save(['children_ids'=>implode(",", $childids)]);
            $prow->updateChildrens();
        }
    }
}
