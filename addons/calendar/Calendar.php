<?php

namespace addons\calendar;

use app\common\library\Menu;
use think\Addons;

/**
 * 日历插件
 */
class Calendar extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'calendar',
                'title'   => '日历管理',
                'icon'    => 'fa fa-calendar',
                'sublist' => [
                    ['name' => 'calendar/index', 'title' => '查看'],
                    ['name' => 'calendar/addevent', 'title' => '添加事件'],
                    ['name' => 'calendar/delevent', 'title' => '删除事件'],
                    ['name' => 'calendar/add', 'title' => '添加日历'],
                    ['name' => 'calendar/edit', 'title' => '编辑日历'],
                    ['name' => 'calendar/del', 'title' => '删除日历'],
                    ['name' => 'calendar/multi', 'title' => '批量更新'],
                ]
            ]
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('calendar');
        return true;
    }
    
    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('calendar');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('calendar');
    }

}
