<?php

namespace app\common\model;

use think\Model;

class ModelLog extends Model
{
    protected $name = 'log';
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'model_text',
        'status_text',
    ];

    protected static $model_id = '';
    protected static $model_type = '';
    //自定义日志标题
    protected static $title = '';
    //自定义日志内容
    protected static $content = '';

    public static function setTitle($title)
    {
        self::$title = $title;
    }

    public static function setContent($content)
    {
        self::$content = $content;
    }

    public static function setModel($model_type, $model_id)
    {
        self::$model_type = $model_type;
        self::$model_id = $model_id;

    }

    public static function record($title = '', $typedata='active', $status='normal')
    {
        $content = self::$content;
        if (!$content) {
            $content = request()->param();
        }
        $title = $title?$title:self::$title;
        self::create([
            'model_type'     => self::$model_type,
            'model_id'     => self::$model_id,
            'title'     => $title,
            'typedata'     => $typedata,
            'status'     => $status,
            'content'   => !is_scalar($content) ? json_encode($content) : $content,
            'url'       => request()->url(),
            'admin_id'  => 0,
            'username'  => "manage",
            'ip'        => request()->ip()
        ]);
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'),'hidden' => __('Hidden'),'locked' => __('Locked')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getModelTextAttr($value, $data) {
        $mm = $this->getAttr("model");
        return $mm?$mm->name:"-";
    }

    public function model()
    {
        return $this->morphTo();
    }
}
