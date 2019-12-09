<?php

namespace app\admin\model;

use think\Config;
use think\Model;

class Modelx extends \app\common\model\Modelx
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'status_text'
    ];


    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden'), 'locked' => __('Locked')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public static function init()
    {
        self::afterInsert(function ($row) {
            $prefix = Config::get('database.prefix');
            $sql = "CREATE TABLE `{$prefix}{$row['table']}` (
                    `id` int(10) AUTO_INCREMENT NOT NULL,
                    `status` enum('normal','hidden', 'locked', 'light') DEFAULT 'normal' COMMENT '状态标志',
                    `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
                    PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='{$row['name']}'";
            db()->query($sql);

            $fieldatas = array(array(
                "model_id" => $row['id'],
                "name" => "createtime",
                "title" => __('createtime'),
                "type" => "datetime",
                "newstatus" => "disabled",
                "editstatus" => "disabled",
                "content" => "",
                "length" => "10",
                "defaultvalue" => "0",
                'relevance'=>'',
                'status'=>"locked"
            ), array(
                "model_id" => $row['id'],
                "name" => "updatetime",
                "title" => __('updatetime'),
                "type" => "datetime",
                "newstatus" => "disabled",
                "editstatus" => "disabled",
                "content" => "",
                "length" => "10",
                "defaultvalue" => "0",
                'relevance'=>'',
                'status'=>"locked"
            ), array(
                "model_id" => $row['id'],
                "name" => "name",
                "title" => "名称",
                "type" => "string",
                "rule" => "required",
                "length" => "50",
                "newstatus" => "normal",
                "editstatus" => "normal",
                "content" => "",
                "defaultvalue" => "",
                'relevance'=>'',
                'status'=>"locked"
            ), array(
                "model_id" => $row['id'],
                "name" => "idcode",
                "title" => "ID",
                "type" => "string",
                "rule" => "required",
                "length" => "50",
                "newstatus" => "disabled",
                "editstatus" => "locked",
                "content" => "",
                "defaultvalue" => "",
                'relevance'=>'',
                'status'=>"locked"
            ), array(
                "model_id" => $row['id'],
                "name" => "creator",
                "title" => __('Creator'),
                "type" => "model",
                "rule" => "required",
                "length" => "10",
                "newstatus" => "hidden",
                "editstatus" => "hidden",
                "content" => "",
                "defaultvalue" => "admin",
                'relevance'=>'',
                'status'=>"locked"
            ), array(
                "model_id" => $row['id'],
                "name" => "owners",
                "title" => __('Owners'),
                "type" => "model",
                "rule" => "required",
                "length" => "255",
                "newstatus" => "hidden",
                "editstatus" => "normal",
                "content" => "",
                "defaultvalue" => "admin",
                'relevance'=>'',
                'status'=>"locked"
            ), array(
                "model_id" => $row['id'],
                "name" => "group",
                "title" => "分组",
                "type" => "model",
                "length" => "255",
                "newstatus" => "normal",
                "editstatus" => "disabled",
                "content" => '{"model":"ModelGroup"}',
                "defaultvalue" => "group",
                'relevance'=>'',
                'status'=>"locked"
            ));
            $fieldatas = (new Fields)->saveAll($fieldatas);

            $scenerydatas = array(array(
                "model_id" => $row['id'],
                "model_table" => $row['table'],
                "table" => $row['table'],
                "name" => "index",
                "title" => __('Default'),
                "status" => "locked",
                "pos" => "index",
                'main' => 1
            ), array(
                "model_id" => $row['id'],
                "model_table" => $row['table'],
                "table" => $row['table'],
                "name" => "view",
                "title" => __('Basic'),
                "status" => "locked",
                "pos" => "view",
                'main' => 1
            ), array(
                "model_id" => $row['id'],
                "model_table" => $row['table'],
                "table" => $row['table'],
                "name" => "block",
                "title" => __('Block'),
                "status" => "locked",
                "pos" => "block",
                'main' => 1
            ), array(
                "model_id" => $row['id'],
                "model_table" => $row['table'],
                "table" => $row['table'],
                "name" => "hinder",
                "title" => __('Hinder'),
                "status" => "locked",
                "pos" => "hinder",
                'main' => 1
            ));
            $scenerydatas = (new Scenery)->saveAll($scenerydatas);

            $sightdatas = array();
            foreach ($scenerydatas as $sv) {
                foreach ($fieldatas as $fv) {
                    $scenerydatas = array(
                        "scenery_id" => $sv["id"],
                        "fields_id" => $fv["id"],
                        "model_id" => $sv['model_id']
                    );
                    $sightdatas [] = $scenerydatas;
                }
            }
            (new Sight)->saveAll($sightdatas);

            $authlist = [
                $row['table']=>[
                    'title'=>$row['name'],
                    'icon'=>'fa fa-th-list',
                    'ismenu'=>'1',
                    'childs'=>[
                        $row['table']."/index"=>[
                            'title'=>$row['name'].'列表',
                            'icon'=>'fa fa-th-list',
                            'ismenu'=>'1',
                            'childs'=>[
                                $row['table']."/index/index"=>[
                                    'title'=>'默认',
                                    'icon'=>'fa fa-circle-o',
                                    'ismenu'=>'0',
                                ],
                            ]
                        ],

                        $row['table']."/view"=>[
                            'title'=>'详细信息',
                            'icon'=>'fa fa-circle-o',
                            'ismenu'=>'0',
                            'childs'=>[
                                $row['table']."/view/view"=>[
                                    'title'=>'基础信息',
                                    'icon'=>'fa fa-circle-o',
                                    'ismenu'=>'0',
                                ],
                            ]
                        ],
                        "log/index/model_type/".$row['table']=>[
                            'title'=>$row['name'].'日志',
                            'icon'=>'fa fa-wpforms',
                            'ismenu'=>'1',
                            'childs'=>[
                                "log/add/model_type/".$row['table']=>[
                                    'title'=>'增加',
                                    'icon'=>'fa fa fa-wpforms',
                                    'ismenu'=>'0',
                                ],
                                "log/edit/model_type/".$row['table']=>[
                                    'title'=>'修改',
                                    'icon'=>'fa fa fa-wpforms',
                                    'ismenu'=>'0',
                                ],
                                "log/del/model_type/".$row['table']=>[
                                    'title'=>'删除',
                                    'icon'=>'fa fa fa-wpforms',
                                    'ismenu'=>'0',
                                ],
                                "log/view/model_type/".$row['table']=>[
                                    'title'=>'详情',
                                    'icon'=>'fa fa fa-wpforms',
                                    'ismenu'=>'0',
                                ],
                            ]
                        ],
                        "group/index/model_type/".$row['table']=>[
                            'title'=>$row['name'].'分组',
                            'icon'=>'fa fa-group',
                            'ismenu'=>'1',
                            'childs'=>[
                                "group/del/model_type/".$row['table']=>[
                                    'title'=>'删除',
                                    'icon'=>'fa fa-circle-o',
                                    'ismenu'=>'0',
                                ],
                                "group/edit/model_type/".$row['table']=>[
                                    'title'=>'修改',
                                    'icon'=>'fa fa-circle-o',
                                    'ismenu'=>'0',

                                ],
                                "group/add/model_type/".$row['table']=>[
                                    'title'=>'增加',
                                    'icon'=>'fa fa-circle-o',
                                    'ismenu'=>'0',
                                ],
                                "group/rule/model_type/".$row['table']=>[
                                    'title'=>'规则',
                                    'icon'=>'fa fa-circle-o',
                                    'ismenu'=>'0',
                                ],
                            ]
                        ],
                        $row['table']."/add"=>[
                            'title'=>'添加',
                            'icon'=>'fa fa-circle-o',
                            'ismenu'=>'0',
                        ],
                        $row['table']."/edit"=>[
                            'title'=>'编辑',
                            'icon'=>'fa fa-circle-o',
                            'ismenu'=>'0',

                        ],
                        $row['table']."/del"=>[
                            'title'=>'删除',
                            'icon'=>'fa fa-circle-o',
                            'ismenu'=>'0',
                        ],
                        $row['table']."/hinder"=>[
                            'title'=>'Hinder',
                            'icon'=>'fa fa-circle-o',
                            'ismenu'=>'0',
                        ],
                        $row['table']."/chart"=>[
                            'title'=>$row['name'].'统计',
                            'icon'=>'fa fa-area-chart',
                            'ismenu'=>'1',
                        ],
                    ],
                ],
            ];


            function authfun($authlist, $pid, $weighbase){
                $weigh = $weighbase + count($authlist);
                $curtime = time();
                foreach ($authlist as $ak=>$av) {
                    $data = [
                        'type'=>'file',
                        'pid'=>$pid,
                        'name'=>$ak,
                        'title'=>$av['title'],
                        'icon'=>$av['icon'],
                        'ismenu'=>$av['ismenu'],
                        'status'=>'normal',
                        'condition'=>'','remark'=>'',
                        'createtime'=>$curtime,
                        'updatetime'=>$curtime,
                        'weigh'=>$weigh--,
                    ];
                    $r1 = \app\admin\model\AuthRule::create($data);
                    if (isset($av['childs'])) {
                        authfun($av['childs'], $r1['id'], $weigh);
                    }
                }
            };
            authfun($authlist, 0, 0);
        });

        self::afterDelete(function ($row) {
            $prefix = Config::get('database.prefix');
            db()->query("DELETE FROM `{$prefix}fields` WHERE model_id={$row['id']}");
            db()->query("DELETE FROM `{$prefix}scenery` WHERE model_id={$row['id']}");
            db()->query("DELETE FROM `{$prefix}sight` WHERE model_id={$row['id']}");
            db()->query("DROP TABLE  `{$prefix}{$row['table']}`");
        });
    }

}
