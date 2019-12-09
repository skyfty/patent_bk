<?php

namespace app\admin\model;

use app\admin\library\Auth;
use think\Model;

class ModelLog extends \app\common\model\ModelLog
{
    public static function record($title = '', $typedata='active', $status='normal')
    {
        $auth = Auth::instance();
        $admin_id = $auth->isLogin() ? $auth->id : 1;
        $admin_username = $auth->isLogin() ? $auth->username : "manage";

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
            'admin_id'  => $admin_id,
            'username'  => $admin_username,
            'ip'        => request()->ip()
        ]);
    }
}
