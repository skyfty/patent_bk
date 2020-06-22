<?php

namespace app\wxapp\library;

use app\common\library\Token;
use think\Db;

class Auth extends \app\common\library\Auth
{
    /**
     * 获取会员组别规则列表
     * @return array
     */
    public function getRuleList()
    {
        return [];
    }

    /**
     * 获取左侧和顶部菜单栏
     *
     * @param array $params URL对应的badge数据
     * @param string $fixedPage 默认页
     * @return array
     */
    public function getSidebar($params = [], $fixedPage = 'dashboard')
    {
        return [];
    }
    public function check($path = NULL, $module = NULL)
    {
        return $this->_logined;
    }

}
