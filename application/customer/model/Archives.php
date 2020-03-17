<?php

namespace app\customer\model;

use think\Config;
use think\Db;
use think\Model;

/**
 * 文章模型
 */
class Archives Extends Model
{
    protected $name = "cms_archives";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
        'fullurl',
        'likeratio',
        'tagslist',
        'create_date',
    ];

    public function __call($method, $args)
    {
        return parent::__call($method, $args);
    }

    /**
     * 批量设置数据
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    protected static function init()
    {
    }

    public function getCreateDateAttr($value, $data)
    {
        return human_date($data['createtime']);
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : Config::get('default_archives_img');
        return cdnurl($value, true);
    }

    public function getTagslistAttr($value, $data)
    {
        $list = [];
        foreach (array_filter(explode(",", $data['tags'])) as $k => $v) {
            $list[] = ['name' => $v, 'url' => url('tags/index', [':name' => $v])];
        }
        return $list;
    }

    public function getUrlAttr($value, $data)
    {
        return url('archives/view', ['id' => $data['id'],  'channel' => $data['channel_id']]);
    }

    public function getFullUrlAttr($value, $data)
    {
        return url('archives/view', ['id' => $data['id'], 'channel' => $data['channel_id']], true, true);
    }

    public function getLikeratioAttr($value, $data)
    {
        return ($data['dislikes'] > 0 ? min(1, $data['likes'] / ($data['dislikes'] + $data['likes'])) : ($data['likes'] ? 1 : 0.5)) * 100;
    }

    /**
     * 获取文档列表
     * @param $tag
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getArchivesList($tag)
    {
        $channel = !isset($tag['channel']) ? '' : $tag['channel'];
        $tags = empty($tag['tags']) ? '' : $tag['tags'];
        $condition = empty($tag['condition']) ? '' : $tag['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $flag = empty($tag['flag']) ? '' : $tag['flag'];
        $row = empty($tag['row']) ? 10 : (int)$tag['row'];
        $orderby = empty($tag['orderby']) ? 'createtime' : $tag['orderby'];
        $orderway = empty($tag['orderway']) ? 'desc' : strtolower($tag['orderway']);
        $limit = empty($tag['limit']) ? $row : $tag['limit'];
        $cache = !isset($tag['cache']) ? true : (int)$tag['cache'];
        $imgwidth = empty($tag['imgwidth']) ? '' : $tag['imgwidth'];
        $imgheight = empty($tag['imgheight']) ? '' : $tag['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $where = ['status' => 'normal'];

        $where['deletetime'] = ['exp', Db::raw('IS NULL')]; //by erastudio

        if ($channel !== '') {
            $where['channel_id'] = ['in', $channel];
        }
        //如果有设置标志,则拆分标志信息并构造condition条件
        if ($flag !== '') {
            if (stripos($flag, '&') !== false) {
                $arr = [];
                foreach (explode('&', $flag) as $k => $v) {
                    $arr[] = "FIND_IN_SET('{$v}', flag)";
                }
                if ($arr)
                    $condition .= "(" . implode(' AND ', $arr) . ")";
            } else {
                $condition .= ($condition ? ' AND ' : '');
                $arr = [];
                foreach (array_merge(explode(',', $flag), explode('|', $flag)) as $k => $v) {
                    $arr[] = "FIND_IN_SET('{$v}', flag)";
                }
                if ($arr)
                    $condition .= "(" . implode(' OR ', $arr) . ")";
            }
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['createtime', 'updatetime', 'views', 'weigh', 'id']) ? "{$orderby} {$orderway}" : "createtime {$orderway}");

        $archivesModel = self::with('channel');
        // 如果有筛选标签,则采用子查询
        if ($tags) {
            $tagsList = Tags::where('name', 'in', explode(',', $tags))->cache($cache)->limit($limit)->select();
            $archives = [];
            foreach ($tagsList as $k => $v) {
                $archives = array_merge($archives, explode(',', $v['archives']));
            }
            if ($archives) {
                $archivesModel->where('id', 'in', $archives);
            }
        }
        $list = $archivesModel
            ->where($where)
            ->where($condition)
            ->field($field)
            ->cache($cache)
            ->order($order)
            ->limit($limit)
            ->select();

        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    /**
     * 渲染数据
     * @param array $list
     * @param int $imgwidth
     * @param int $imgheight
     * @return array
     */
    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        foreach ($list as $k => &$v) {
            $v['hasimage'] = $v['image'] ? true : false;
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['title'] . '</a>';
            $v['channellink'] = '<a href="' . $v['channel']['url'] . '">' . $v['channel']['name'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    /**
     * 获取分页列表
     * @param array $list
     * @param array $tag
     * @return array
     */
    public static function getPageList($list, $tag)
    {
        $imgwidth = empty($tag['imgwidth']) ? '' : $tag['imgwidth'];
        $imgheight = empty($tag['imgheight']) ? '' : $tag['imgheight'];
        return self::render($list, $imgwidth, $imgheight);
    }

    /**
     * 获取分页信息
     * @param array $list
     * @param array $tag
     * @return string
     */
    public static function getPageInfo($list, $tag)
    {
        return '';
    }

    /**
     * 获取分页过滤
     * @param array $list
     * @param array $tag
     * @return array
     */
    public static function getPageFilter($list, $tag)
    {
        $exclude = empty($tag['exclude']) ? '' : $tag['exclude'];
        return $list;
    }

    /**
     * 获取分页排序
     * @param array $list
     * @param array $tag
     * @return array
     */
    public static function getPageOrder($list, $tag)
    {
        $exclude = empty($tag['exclude']) ? '' : $tag['exclude'];
        return $list;
    }

    /**
     * 获取上一页下一页
     * @param string $type
     * @param string $archives
     * @param string $channel
     * @return array
     */
    public static function getPrevNext($type, $archives, $channel)
    {
        $model = self::where('id', $type === 'prev' ? '<' : '>', $archives)->where('status', 'normal');
        if ($channel !== '') {
            $model->where('channel_id', 'in', $channel);
        }
        $model->order($type === 'prev' ? 'id desc' : 'id asc');
        $row = $model->find();
        return $row;
    }

    /**
     * 关联栏目模型
     */
    public function channel()
    {
        return $this->belongsTo("Channel")->field('id,name,image,diyname,items')->setEagerlyType(1);
    }

}
