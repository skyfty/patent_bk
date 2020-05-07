<?php
/**
 * Created by PhpStorm.
 * User: feiti
 * Date: 2020/5/7
 * Time: 16:35
 */

namespace app\common\model;

use think\model\relation\HasMany;

class HasManyComma extends HasMany
{
    /**
     * 执行基础查询（进执行一次）
     * @access protected
     * @return void
     */
    protected function baseQuery()
    {
        if (empty($this->baseQuery)) {
            if (isset($this->parent->{$this->localKey})) {
                // 关联查询带入关联条件
                $this->query->where($this->foreignKey,"in", $this->parent->{$this->localKey});
            }
            $this->baseQuery = true;
        }
    }
}