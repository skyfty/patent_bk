<?php

namespace addons\address\controller;

use think\addons\Controller;

class Index extends Controller
{
    public function select()
    {
        $config = get_addon_config('address');
        $lat = $this->request->get('lat', $config['lat']);
        $lng = $this->request->get('lng', $config['lng']);
        $range = $this->request->get('range',0);
        $setpoint = $this->request->get('setpoint', 0);

        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->assign('range', $range);
        $this->assign('setpoint', $setpoint);
        $this->assign('location', $config['location']);
        return $this->fetch('index/' . $config['maptype']);
    }

}
