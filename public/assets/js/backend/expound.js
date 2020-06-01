define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree','sortable','backend/preset'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree,Sortable, Preset) {
    var Controller = {
        api: {
        }
    };
    Controller.api = $.extend(Preset.api, Controller.api);
    return $.extend(Preset, Controller);
});