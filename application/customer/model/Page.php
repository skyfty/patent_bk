<?php

namespace app\customer\model;

use think\Model;

/**
 * 单页模型
 */
class Page Extends Model
{

    protected $name = "cms_page";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
        'url',
        'fullurl'
    ];

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : Config::get('default_page_img');
        return cdnurl($value);
    }

    public function getUrlAttr($value, $data)
    {
        return url('page/index', [':diyname' => $data['diyname']]);
    }

    public function getFullurlAttr($value, $data)
    {
        return url('page/index', [':diyname' => $data['diyname']], true, true);
    }

    /**
     * 获取单页列表
     * @param $params
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getPageList($params)
    {
        $name = empty($params['name']) ? '' : $params['name'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'nums' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $cache = !isset($params['cache']) ? true : (int)$params['cache'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';

        $where = [];
        if ($name !== '') {
            $where['name'] = $name;
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['name', 'id', 'createtime', 'updatetime']) ? "{$orderby} {$orderway}" : "id {$orderway}");

        $list = self::where($where)
            ->where($condition)
            ->field($field)
            ->order($order)
            ->limit($limit)
            ->cache($cache)
            ->select();
        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        foreach ($list as $k => &$v) {
            $v['hasimage'] = $v->getData('image') ? true : false;
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['title'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

}
