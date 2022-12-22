<?php

// 公共助手函数

use app\common\library\Lunar;


if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int    $size      大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int    $time   时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }
}

if (!function_exists('human_date')) {

    /**
     * 获取语义化时间
     * @param int $time  时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }
}

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string  $url    资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $url = preg_match($regex, $url) ? $url : \think\Config::get('upload.cdnurl') . $url;
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}


if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param    string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }
}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest   目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }
}

if (!function_exists('addtion')) {

    /**
     * 附加关联字段数据
     * @param array $items  数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields) {
            return $items;
        }
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model'];
            } else {
                $model = $v['name'] ? \think\Db::name($v['name']) : \think\Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = $model->where($primary, 'in', $ids[$v['field']])->column("{$primary},{$v['column']}");
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $v[$fieldsArr[$n]['display']] = implode(',', array_intersect_key($result[$n], $curr));
                }
            }
        }
        return $items;
    }
}

if (!function_exists('var_export_short')) {

    /**
     * 返回打印数组结构
     * @param string $var    数组
     * @param string $indent 缩进字符
     * @return string
     */
    function var_export_short($var, $indent = "")
    {
        switch (gettype($var)) {
            case "string":
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : var_export_short($key) . " => ")
                        . var_export_short($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "TRUE" : "FALSE";
            default:
                return var_export($var, true);
        }
    }
}

if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" alignment-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}

if (!function_exists('date2week')) {

    function date2week($date)
    {
        $weekarray=array("日","一","二","三","四","五","六"); //先定义一个数组
        return "周".$weekarray[date("w", $date)];
    }
}


if (!function_exists('date2lunar')) {

    function date2lunar($date)
    {
        $lunar=new Lunar();
        $today=$lunar->convertSolarToLunar(date('Y',$date),date('m',$date),date('d',$date));
        return $today[2];
       // $festival = $lunar->getFestival(date('Y-m-d',$date));
    }
}

if (!function_exists('date2age')) {

    function date2age($date,$type = 1)
    {
        $nowYear = date("Y",time());
        $nowMonth = date("m",time());
        $nowDay = date("d",time());
        $birthYear = date("Y",$date);
        $birthMonth = date("m",$date);
        $birthDay = date("d",$date);
        if($type == 1){
            $age = $nowYear - ($birthYear - 1);
        }elseif( $type == 2){
            if($nowMonth<$birthMonth){
                $age = $nowYear - $birthYear - 1;
            }elseif($nowMonth==$birthMonth){
                if($nowDay<$birthDay){
                    $age = $nowYear - $birthYear - 1;
                }else{
                    $age = $nowYear - $birthYear;
                }
            }else{
                $age = $nowYear - $birthYear;
            }
        }
        return $age;
    }
}

if (!function_exists('swap_var')) {

    function swap_var(&$v1, &$v2)
    {
        list($v1, $v2) = [$v2, $v1];
    }
}




if (!function_exists('default_avatar')) {

    /**
     * 生成单选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function default_avatar($avatar)
    {
        return $avatar?$avatar:"/assets/img/customer.png";
    }
}




if (!function_exists('listfield')) {

    /**
     * 生成单选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function listfield($model, $name)
    {
        $field = model("fields")->cache(true)->where("model_table", $model)->where("name", $name)->find();
        return $field?$field->content_list:[];
    }
}


if (!function_exists('thumbnail')) {

    /**
     * 生成单选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function thumbnail($path, $width, $hegiht) {
        //打开文件
        if ($path == "")
            return $path;
        $file_name_pos = strrpos($path, "/");
        $thumbnail_url = '/thumbnail/'."thumb_".$width."_".$hegiht."_".substr($path, $file_name_pos + 1);
        $thumbnail_full_path = ROOT_PATH . '/public'.$thumbnail_url;
        if (!file_exists($thumbnail_full_path)) {
            $image = \think\Image::open(ROOT_PATH . '/public' . $path);
            $image->thumb($width, $hegiht)->save($thumbnail_full_path);
        }
        return file_exists($thumbnail_full_path)?$thumbnail_url:$path;
    }
}

function build_where_param($sym,$k,$v,$relationSearch=null) {
    $where = [];
    switch ($sym) {
        case '=':
        case '!=':
            $where = [$k, $sym, (string)$v];
            break;
        case 'LIKE':
        case 'NOT LIKE':
        case 'LIKE %...%':
        case 'NOT LIKE %...%':
            $where = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
            break;
        case '>':
        case '>=':
        case '<':
        case '<=':
            $where = [$k, $sym, intval($v)];
            break;
        case 'FINDIN':
        case 'FINDINSET':
        case 'FIND_IN_SET':
            $where = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
            break;
        case 'IN':
        case 'IN(...)':
        case 'NOT IN':
        case 'NOT IN(...)':
            $where = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
            break;
        case 'BETWEEN':
        case 'NOT BETWEEN':
            $v = str_replace(' - ', ',', $v);
            $arr = array_slice(explode(',', $v), 0, 2);
            if (stripos($v, ',') === false || !array_filter($arr))
                continue;
            //当出现一边为空时改变操作符
            if ($arr[0] === '') {
                $sym = $sym == 'BETWEEN' ? '<=' : '>';
                $arr = $arr[1];
            } else if ($arr[1] === '') {
                $sym = $sym == 'BETWEEN' ? '>=' : '<';
                $arr = $arr[0];
            }
            $where = [$k, $sym, $arr];
            break;
        case 'RANGE':
        case 'NOT RANGE':
            $v = str_replace(' - ', ',', $v);
            $arr = array_slice(explode(',', $v), 0, 2);
            if (stripos($v, ',') === false || !array_filter($arr))
                continue;
            //当出现一边为空时改变操作符
            if ($arr[0] === '') {
                $sym = $sym == 'RANGE' ? '<=' : '>';
                $arr = $arr[1];
            } else if ($arr[1] === '') {
                $sym = $sym == 'RANGE' ? '>=' : '<';
                $arr = $arr[0];
            }
            $where = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
            break;
        case 'NULL':
        case 'IS NULL':
        case 'NOT NULL':
        case 'IS NOT NULL':
            $where = [$k, strtolower(str_replace('IS ', '', $sym))];
            break;
        case 'QJSON':
            $where = [$k, 'LIKE', "%{$v}%"];
            break;
        case 'BETWEEN TIME':
        case 'NOT BETWEEN TIME':{
            if (is_string($v)) {
                $v = explode(" - ",$v);
                if (count($v) == 2) {
                    $v[0] = strtotime($v[0]." 00:00:00");
                    $v[1] = strtotime($v[1]." 23:59:59");
                }
            }
            $where = [$k, $sym, $v];
            break;
        }
        case 'LTB': {
            $v = explode(" - ",$v);
            $where = [$k, "<= time", $v[0]];
            break;
        }
        case 'GTB': {
            $v = explode(" - ",$v);
            $where = [$k, ">= time", $v[0]];
            break;
        }
        case 'LTE': {
            $v = explode(" - ",$v);
            $where = [$k, "<= time", $v[1]];
            break;
        }
        case 'GTE': {
            $v = explode(" - ",$v);
            $where = [$k, ">= time", $v[1]];
            break;
        }
        default: {
            $where = [$k, $sym, $v];
            break;

        }
    }
    return $where;
}