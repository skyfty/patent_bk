<?php

namespace app\common\behavior;
use think\Config;


/**
 * Alisms
 */
class Alisms
{
    /**
     * 短信发送行为
     * @param   Sms     $params
     * @return  boolean
     */
    public function smsSend(&$params)
    {
        $config = Config::get('sms');

        $alisms = new \app\common\library\Alisms();
        $result = $alisms->mobile($params->mobile)
                ->template($config['ali_template'][$params->event])
                ->param(['code' => $params->code])
                ->send();
        return $result;
    }

    /**
     * 短信发送通知
     * @param   array   $params
     * @return  boolean
     */
    public function smsNotice(&$params)
    {
        $alisms = new \app\common\library\Alisms();
        $result = $alisms->mobile($params['mobile'])
                ->template($params['ali_template'])
                ->param($params)
                ->send();
        return $result;
    }

    /**
     * 检测验证是否正确
     * @param   Sms     $params
     * @return  boolean
     */
    public function smsCheck(&$params)
    {
        return TRUE;
    }

}
