<?php

use app\common\model\Category;
use fast\Form;
use fast\Tree;
use think\Db;

if (!function_exists('build_select')) {

    /**
     * 生成下拉列表
     * @param string $name
     * @param mixed $options
     * @param mixed $selected
     * @param mixed $attr
     * @return string
     */
    function build_select($name, $options, $selected = [], $attr = [])
    {
        $options = is_array($options) ? $options : explode(',', $options);
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        return Form::select($name, $options, $selected, $attr);
    }
}

if (!function_exists('build_radios')) {

    /**
     * 生成单选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function build_radios($name, $list = [], $selected = null)
    {
        $html = [];
        $selected = is_null($selected) ? key($list) : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        foreach ($list as $k => $v) {
            $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::radio($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
        }
        return '<div class="radio">' . implode(' ', $html) . '</div>';
    }
}

if (!function_exists('build_checkboxs')) {

    /**
     * 生成复选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function build_checkboxs($name, $list = [], $selected = null)
    {
        $html = [];
        $selected = is_null($selected) ? [] : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        foreach ($list as $k => $v) {
            $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::checkbox($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
        }
        return '<div class="checkbox">' . implode(' ', $html) . '</div>';
    }
}


if (!function_exists('build_category_select')) {

    /**
     * 生成分类下拉列表框
     * @param string $name
     * @param string $type
     * @param mixed $selected
     * @param array $attr
     * @param array $header
     * @return string
     */
    function build_category_select($name, $type, $selected = null, $attr = [], $header = [])
    {
        $tree = Tree::instance();
        $tree->init(Category::getCategoryArray($type), 'pid');
        $categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = $header ? $header : [];
        foreach ($categorylist as $k => $v) {
            $categorydata[$v['id']] = $v['name'];
        }
        $attr = array_merge(['id' => "c-{$name}", 'class' => 'form-control selectpicker'], $attr);
        return build_select($name, $categorydata, $selected, $attr);
    }
}

if (!function_exists('build_toolbar')) {

    /**
     * 生成表格操作按钮栏
     * @param array $btns 按钮组
     * @param array $attr 按钮属性值
     * @return string
     */
    function build_toolbar($btns = NULL, $attr = [], $controller = NULL)
    {
        $auth = \app\admin\library\Auth::instance();
        if ($controller == NULL) {
            $controller = str_replace('.', '/', strtolower(think\Request::instance()->controller()));
        }
        $btns = $btns ? $btns : ['refresh', 'add', 'edit', 'del', 'import'];
        $btns = is_array($btns) ? $btns : explode(',', $btns);
        $index = array_search('delete', $btns);
        if ($index !== false) {
            $btns[$index] = 'del';
        }
        $btnAttr = [
            'refresh' => ['javascript:;', 'btn btn-primary btn-refresh', 'fa fa-refresh', '', __('Refresh')],
            'add'     => ['javascript:;', 'btn btn-success btn-add', 'fa fa-plus', __(''), __('Add')],
            'edit'    => ['javascript:;', 'btn btn-success btn-edit btn-disabled disabled', 'fa fa-pencil', __(''), __('Edit')],
            'del'     => ['javascript:;', 'btn btn-danger btn-del btn-disabled disabled', 'fa fa-trash', __(''), __('Delete')],
            'import'  => ['javascript:;', 'btn btn-info btn-import', 'fa fa-upload', __(''), __('Import')],
        ];
        $btnAttr = array_merge($btnAttr, $attr);
        $html = [];
        foreach ($btns as $k => $v) {
            //如果未定义或没有权限
            if (!isset($btnAttr[$v]) || ($v !== 'refresh' && !$auth->check("{$controller}/{$v}"))) {
                continue;
            }
            list($href, $class, $icon, $text, $title) = $btnAttr[$v];
            //$extend = $v == 'import' ? 'id="btn-import-file" data-url="ajax/upload" data-mimetype="csv,xls,xlsx" data-multiple="false"' : '';
            //$html[] = '<a href="' . $href . '" class="' . $class . '" title="' . $title . '" ' . $extend . '><i class="' . $icon . '"></i> ' . $text . '</a>';
            if ($v == 'import') {
                $template = str_replace('/', '_', $controller);
                $download = '';
                if (file_exists("./template/{$template}.xlsx")) {
                    $download .= "<li><a href=\"/template/{$template}.xlsx\" target=\"_blank\">XLSX模版</a></li>";
                }
                if (file_exists("./template/{$template}.xls")) {
                    $download .= "<li><a href=\"/template/{$template}.xls\" target=\"_blank\">XLS模版</a></li>";
                }
                if (file_exists("./template/{$template}.csv")) {
                    $download .= empty($download) ? '' : "<li class=\"divider\"></li>";
                    $download .= "<li><a href=\"/template/{$template}.csv\" target=\"_blank\">CSV模版</a></li>";
                }
                $download .= empty($download) ? '' : "\n                            ";
                if (!empty($download)) {
                    $html[] = <<<EOT
                        <div class="btn-group">
                            <button type="button" href="{$href}" class="btn btn-info btn-import" title="{$title}" id="btn-import-file" data-url="ajax/upload" data-mimetype="csv,xls,xlsx" data-multiple="false"><i class="{$icon}"></i> {$text}</button>
                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" title="下载批量导入模版">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">{$download}</ul>
                        </div>
EOT;
                } else {
                    $html[] = '<a href="' . $href . '" class="' . $class . '" title="' . $title . '" id="btn-import-file" data-url="ajax/upload" data-mimetype="csv,xls,xlsx" data-multiple="false"><i class="' . $icon . '"></i> ' . $text . '</a>';
                }
            } else {
                $html[] = '<a href="' . $href . '" class="' . $class . '" title="' . $title . '"><i class="' . $icon . '"></i> ' . $text . '</a>';
            }
        }
        return implode(' ', $html);
    }
}

if (!function_exists('build_heading')) {

    /**
     * 生成页面Heading
     *
     * @param string $path 指定的path
     * @return string
     */
    function build_heading($path = null, $container = true)
    {
        $title = $content = '';
        if (is_null($path)) {
            $action = request()->action();
            $controller = str_replace('.', '/', request()->controller());
            $path = strtolower($controller . ($action && $action != 'index' ? '/' . $action : ''));
        }
        // 根据当前的URI自动匹配父节点的标题和备注
        $data = Db::name('auth_rule')->where('name', $path)->field('title,remark')->find();
        if ($data) {
            $title = __($data['title']);
            $content = __($data['remark']);
        }
        if (!$content) {
            return '';
        }
        $result = '<div class="panel-lead"><em>' . $title . '</em>' . $content . '</div>';
        if ($container) {
            $result = '<div class="panel-heading">' . $result . '</div>';
        }
        return $result;
    }
}


if (!function_exists('select_content')) {

    /**
     * 生成页面Heading
     *
     * @param string $path 指定的path
     * @return string
     */
    function select_content($model_table, $name)
    {
        return model("fields")->where("model_table", $model_table)->where("name", $name)->find()->content_list;
    }
}


if (!function_exists('check_adscription')) {

    function check_adscription($row, $staff) {
        if (!$staff) return true;
        return $row['branch_model_id'] == $staff['branch_model_id'];
    }
}


if (!function_exists('date_text')) {

    function date_text()
    {
        $week = date("w");
        $array = ["周日","周一","周二","周三","周四","周五","周六"];
        return date("Y年m月d日").$array[$week];
    }
}


if (!function_exists('build_branch_select')) {

    /**
     * 生成分类下拉列表框
     * @param string $name
     * @param string $type
     * @param mixed $selected
     * @param array $attr
     * @param array $header
     * @return string
     */
    function build_branch_select($name, $selected = -1, $attr = [])
    {
        $branchdata = [];
        $branchlist = model("branch")->cache(true)->select();
        foreach ($branchlist as $k => $v) {
            $branchdata[$v['id']] = $v['name'];
        }
        $attr = array_merge([
            'id' => "c-{$name}",
            'class' => 'form-control selectpicker',
            'data-selected-text-format'=>'count > 2',
            'live-search'=>'true',
            'title'=>'选择机构'

        ],$attr);
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        return Form::selects($name, $branchdata, $selected, $attr);
    }
}

function formatPresetParams($params) {
    $detail = [];
    foreach(['primary','second','third','entire'] as $mv){
        if (isset($params[$mv])) {
            if ($params[$mv]['warehouse']['id']) {
                $params[$mv."_warehouse_model_id"] = $params[$mv]['warehouse']['id'];
                $warehouseDetail = [];
                $notexistbody = true;
                $warehouse = model("warehouse")->get($params[$mv]['warehouse']['id']);

                foreach(['assembly','behavior'] as $k=>$mm) {
                    if (isset($params[$mv][$mm])) {
                        $mm2 = $params[$mv][$mm];
                        $mmmodel = model($mm)->get($mm2['id']);
                        if (isset($mm2['adjective']) && $mm2['id']) {
                            $params[$mv][$mm]['adjective_list'] = $mmmodel->formatAdjective($mm2['adjective']);
                            if(isset($mm2['status']) && $mm2['status'] == 1) {
                                $warehouseDetail = array_merge($warehouseDetail,$params[$mv][$mm]['adjective_list']);
                            }
                        }
                        if ($mm == 'assembly') {
                            if ($mmmodel['body'] == 1) {
                                $notexistbody = false;
                            }
                        }
                        if (isset($mm2['condition'])) {
                            if ($mmmodel['template'] == 'text' || $mmmodel['template'] == 'string') {      //文本
                                $warehouseDetail[] = ['type'=>$mmmodel['template'],'data'=>$params[$mv]['behavior']['condition']];
                            }elseif($mmmodel['template'] == 'image') {
                                $warehouseDetail[] = ['type'=>$mmmodel['template'],'data'=>"<a  class='preset-condition-image' target='_blank' href='".$params[$mv]['behavior']['condition']."'>"."<img class='preset-condition-img' src='".$params[$mv]['behavior']['condition']."'/></a>"];
                            }elseif($mmmodel['template'] == 'sound' || $mmmodel['template'] == 'video') {
                                $warehouseDetail[] = ['type'=>$mmmodel['template'],'data'=>"<a  class='preset-condition preset-condition-".$mmmodel['template']."' target='_blank' href='".$params[$mv]['behavior']['condition']."'>".$warehouse['name']."</a>"];
                            }
                        }

                    }
                }
                if ($notexistbody && $warehouse['inside'] != 1) {
                    array_splice($warehouseDetail, 0,0, [ ['type'=>"warehouse",'data'=>$warehouse['name']], ['type'=>"nihility",'data'=>"的"], ]);
                }
                $params[$mv] = json_encode($params[$mv], JSON_UNESCAPED_UNICODE);
                $detail = array_merge($detail,$warehouseDetail);
            } else {
                unset($params[$mv]);
            }
        }
    };
    $params['detail'] = json_encode($detail, JSON_UNESCAPED_UNICODE);
    return $params;
}