<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 2018/12/3
 * Time: 20:52
 */
namespace app\common\library\traits;

trait Buildparam
{

    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed $searchfields 快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    protected function buildparams($searchfields = null, $relationSearch = null)
    {
        if (is_null($searchfields)) {
            $searchfields = (array)$this->request->request("searchField/a");
            $searchfields = $searchfields ? $searchfields:$this->searchFields;
        }
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->request->get("search", '');
        $filter = $this->request->get("filter", '');
        $op = $this->request->get("op", '', 'trim');
        $sort = $this->request->get("sort", "id");
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset", 0);
        $limit = $this->request->get("limit", 0);
        $custom = (array)$this->request->request("custom/a");

        $filter = (array)json_decode($filter, TRUE);
        $op = (array)json_decode($op, TRUE);
        $filter = $filter ? $filter : [];
        $where = [];
        $tableName = '';
        if ($relationSearch) {
            if (!empty($this->model)) {
                $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
                $tableName = $name . '.';
            }
            $sortArr = explode(',', $sort);
            foreach ($sortArr as $index => & $item) {
                $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
            }
            unset($item);
            $sort = implode(',', $sortArr);
        }

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$tableName . $this->dataLimitField, 'in', $adminIds];
        }


        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        foreach ($filter as $k => $v) {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k,"|") !== false) {
                $kv = explode("|", $k);
                foreach($kv as $kvk=>$kvv) {
                    if (stripos($kvv, ".") === false) {
                        $kv[$kvk] = $tableName . $kvv;
                    }
                }
                $k = implode("|", $kv);
            } else if (stripos($k, ".") === false) {
                $k = $tableName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            switch ($sym) {
                case '=':
                case '!=':
                    $where[] = [$k, $sym, (string)$v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $where[] = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
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
                    $where[] = [$k, $sym, $arr];
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
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                case 'QJSON':
                    $where[] = [$k, 'LIKE', "%{$v}%"];
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
                    $where[] = [$k, $sym, $v];
                    break;
                }
                case 'LTB': {
                    $v = explode(" - ",$v);
                    $where[] = [$k, "<= time", $v[0]];
                    break;
                }
                case 'GTB': {
                    $v = explode(" - ",$v);
                    $where[] = [$k, ">= time", $v[0]];
                    break;
                }
                case 'LTE': {
                    $v = explode(" - ",$v);
                    $where[] = [$k, "<= time", $v[1]];
                    break;
                }
                case 'GTE': {
                    $v = explode(" - ",$v);
                    $where[] = [$k, ">= time", $v[1]];
                    break;
                }
                default: {
                    $where[] = [$k, $sym, $v];
                    break;

                }
            }
        }
        $where = function ($query) use ($where,$custom) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
            if ($custom && is_array($custom)) {
                foreach ($custom as $k => $v) {
                    if (is_array($v)) {
                        $query->where($k, trim($v[0]), $v[1]);
                    } else {
                        $query->where($k, '=', $v);
                    }
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit];
    }

}