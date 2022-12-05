require.config({
    packages: [{
        name: 'moment',
        location: '../libs/moment',
        main: 'moment'
    }
    ],
    //在打包压缩时将会把include中的模块合并到主文件中
    include: ['css',
        'layer',
        'toastr',
        'fast',
        'cosmetic',
        'backend',
        'backend-init',
        'table',
        'form',
        'dragsort',
        'drag',
        'drop',
        'addtabs',
        'selectpage',
    ],
    paths: {
        'lang': "empty:",
        'form': 'require-form',
        'table': 'require-table',
        'upload': 'require-upload',
        'validator': 'require-validator',
        'drag': 'jquery.drag.min',
        'drop': 'jquery.drop.min',
        'echarts': 'echarts.min',
        'echarts-theme': 'echarts-theme',
        'adminlte': 'adminlte',
        'bootstrap-table-commonsearch': 'bootstrap-table-commonsearch',
        'bootstrap-table-template': 'bootstrap-table-template',
        //
        // 以下的包从bower的libs目录加载
        'jquery': '../libs/jquery/dist/jquery.min',
        'bootstrap': '../libs/bootstrap/dist/js/bootstrap.min',
        'bootstrap-datetimepicker': '../libs/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min',
        'bootstrap-daterangepicker': '../libs/bootstrap-daterangepicker/daterangepicker',
        'bootstrap-select': '../libs/bootstrap-select/dist/js/bootstrap-select.min',
        'bootstrap-select-lang': '../libs/bootstrap-select/dist/js/i18n/defaults-zh_CN',
        'bootstrap-table': '../libs/bootstrap-table/dist/bootstrap-table.min',
        'bootstrap-table-export': '../libs/bootstrap-table/dist/extensions/export/bootstrap-table-export.min',
        'bootstrap-table-mobile': '../libs/bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile',
        'bootstrap-table-lang': '../libs/bootstrap-table/dist/locale/bootstrap-table-zh-CN',
        'bootstrap-slider': '../libs/bootstrap-slider/bootstrap-slider',
        'tableexport': '../libs/tableExport.jquery.plugin/tableExport.min',
        'dragsort': '../libs/fastadmin-dragsort/jquery.dragsort',
        'sortable': '../libs/Sortable/Sortable.min',
        'addtabs': '../libs/fastadmin-addtabs/jquery.addtabs',
        'slimscroll': '../libs/jquery-slimscroll/jquery.slimscroll',
        'validator-core': '../libs/nice-validator/dist/jquery.validator',
        'validator-lang': '../libs/nice-validator/dist/local/zh-CN',
        'plupload': '../libs/plupload/js/plupload.min',
        'toastr': '../libs/toastr/toastr',
        'jstree': '../libs/jstree/dist/jstree.min',
        'layer': '../libs/fastadmin-layer/dist/layer',
        'cookie': '../libs/jquery.cookie/jquery.cookie',
        'cxselect': '../libs/fastadmin-cxselect/js/jquery.cxselect',
        'template': '../libs/art-template/dist/template-native',
        'selectpage': '../libs/fastadmin-selectpage/selectpage',
        'citypicker': '../libs/fastadmin-citypicker/dist/js/city-picker.min',
        'citypicker-data': '../libs/fastadmin-citypicker/dist/js/city-picker.data',
        'bootstrap-treegrid': '../libs/bootstrap-table/dist/extensions/treegrid/bootstrap-table-treegrid',
        'angular': '../libs/angular/angular.min',
        'jquery-ui': '../libs/jquery-ui/jquery-ui.min',
        "qtip2": '../libs/qtip2/jquery.qtip.min',
        'fullcalendar': '../libs/fullcalendar/dist/fullcalendar.min',
        'fullcalendar-lang': '../libs/fullcalendar/dist/locale/zh-cn',
        'scheduler': '../libs/fullcalendar-scheduler/dist/scheduler',
        'jqtable': '../libs/jqtable/js/jqTable',
        'colorpicker': '../libs/bootstrap-colorpicker/dist/js/bootstrap-colorpicker',
        'bootstrap-switch': '../libs/bootstrap-switch/dist/js/bootstrap-switch',
        'datetimepicker': '../libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min',
        'datetimepicker-lang': '../libs/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN',
        'treegrid': '../libs/jquery-treegrid/js/jquery.treegrid',
        'cascader': '../libs/cascader/js/cascader',
        'ztree': '../libs/zTree/js/jquery.ztree.all',
        'jsoneditor': '../libs/jquery-jsoneditor/jquery.jsoneditor.min',
        'webcam': '../libs/webcamjs/webcam.min',
        'jquery-cropper': '../libs/jquery-cropper/dist/jquery-cropper.min',
        'cropperjs': '../libs/cropper/dist/cropper.min',
    },
    // shim依赖配置
    shim: {
        'addons': ['backend'],
        'bootstrap': ['jquery'],
        'bootstrap-table': {
            deps: [
                'bootstrap',
//                'css!../libs/bootstrap-table/dist/bootstrap-table.min.css'
            ],
            exports: '$.fn.bootstrapTable'
        },
        'bootstrap-table-lang': {
            deps: ['bootstrap-table'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-table-export': {
            deps: ['bootstrap-table', 'tableexport'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-table-mobile': {
            deps: ['bootstrap-table'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-table-advancedsearch': {
            deps: ['bootstrap-table'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-table-commonsearch': {
            deps: ['bootstrap-table'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-table-template': {
            deps: ['bootstrap-table', 'template'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'tableexport': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'slimscroll': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'adminlte': {
            deps: ['bootstrap', 'slimscroll'],
            exports: '$.AdminLTE'
        },
        'bootstrap-datetimepicker': [
            'moment/locale/zh-cn',
        ],
        'bootstrap-select-lang': ['bootstrap-select'],
        'jstree': ['css!../libs/jstree/dist/themes/default/style.css',],
        'plupload': {
            deps: ['../libs/plupload/js/moxie.min'],
            exports: "plupload"
        },
        'validator-lang': ['validator-core'],
        'citypicker': ['citypicker-data', 'css!../libs/fastadmin-citypicker/dist/css/city-picker.css'],
        'ztree': {
            deps: [
                'css!../libs/zTree/css/zTreeStyle/zTreeStyle.css',
            ]
        },
        'jsoneditor': {
            deps: [
                '../libs/jquery-jsoneditor/json2',
                'css!../libs/jquery-jsoneditor/jsoneditor.css',
            ]
        },
        'datetimepicker-lang': [
            'datetimepicker',
        ],
        'bootstrap-treegrid': {
            deps: ['bootstrap-table', 'treegrid'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'angular': {
            exports: 'angular'
        },
        'qtip2': ['css!../libs/qtip2/jquery.qtip.css',],
        'fullcalendar-lang': ['fullcalendar'],
        'scheduler': [
            'jquery-ui',
            'fullcalendar',
            'fullcalendar-lang'
        ],
        'colorpicker': {
            deps: [
                'bootstrap',
            ],
            exports: "colorpicker"
        },
        'bootstrap-switch': {
            deps: [
                'bootstrap',
            ],
        },
        'jquery-cropper': {
            deps: [
                'cropperjs',
            ],
        },
        'cropperjs':{
            deps: [
                'css!../libs/cropper/dist/cropper.min.css',
            ],
            exports: "cropper"
        }
    },
    baseUrl: requirejs.s.contexts._.config.config.site.cdnurl + '/assets/js/', //资源基础路径
    map: {
        '*': {
            'css': '../libs/require-css/css.min'
        }
    },
    waitSeconds: 0,
    charset: 'utf-8' // 文件编码
});

require(['jquery', 'bootstrap'], function ($, undefined) {
    //初始配置
    var Config = requirejs.s.contexts._.config.config;
    //将Config渲染到全局
    window.Config = Config;
    // 配置语言包的路径
    var paths = {};
    paths['lang'] = Config.moduleurl + '/ajax/lang?callback=define&controllername=' + Config.controllername;
    // 避免目录冲突
    paths['backend/'] = 'backend/';
    require.config({paths: paths});

    // 初始化
    $(function () {
        require(['fast'], function (Fast) {
            require(['backend', 'backend-init', 'addons','cosmetic'], function (Backend, undefined, undefined, undefined) {
                //加载相应模块
                if (Config.jsname) {
                    require([Config.jsname], function (Controller) {
                        //创建angularJS模块
                        if (Controller[Config.actionname]) {
                            Controller[Config.actionname]();
                        } else if (Controller["defaultAction"]) {
                            Controller["defaultAction"]();
                        }
                        Angular.bootstrap(document, ['app']);
                    }, function (e) {
                        console.error(e);
                    });
                }
            });
        });
    });
});
