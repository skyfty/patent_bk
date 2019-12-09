<?php

//配置文件
return [
    'default_controller'         => 'index',
    'theme'       => "default",
    'url_html_suffix'        => '',
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => APP_PATH . 'customer' . DS . 'view' . DS.'default' . DS . 'common' . DS . 'success.html',
    'dispatch_error_tmpl'    => APP_PATH . 'customer' . DS . 'view' . DS.'default' . DS . 'common' . DS . 'error.html',
];
