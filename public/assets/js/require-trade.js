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
        'dragsort',
        'drag',
        'drop',
        'trade',
        'table',
        'form',
    ],
    paths: {
        'lang': "empty:",
        'form': 'require-form',
        'table': 'require-table',
        'upload': 'require-upload',

        'drag': 'jquery.drag.min',
        'drop': 'jquery.drop.min',
        'validator': 'require-validator',
        //
        // 以下的包从bower的libs目录加载
        'jquery': '../libs/jquery/dist/jquery.min',
        'bootstrap': '../libs/bootstrap/dist/js/bootstrap.min',
        'validator-core': '../libs/nice-validator/dist/jquery.validator',
        'validator-lang': '../libs/nice-validator/dist/local/zh-CN',
        'toastr': '../libs/toastr/toastr',
        'layer': '../libs/fastadmin-layer/dist/layer',
        'cookie': '../libs/jquery.cookie/jquery.cookie',
        'template': '../libs/art-template/dist/template-native',
        'jquery-ui': '../libs/jquery-ui/jquery-ui.min',
        'plupload': '../libs/plupload/js/plupload.min',
        "qtip2": '../libs/qtip2/jquery.qtip.min',
        'bootstrap-table': '../libs/bootstrap-table/dist/bootstrap-table.min',
        'bootstrap-select-lang': '../libs/bootstrap-select/dist/js/i18n/defaults-zh_CN',
        'bootstrap-table-export': '../libs/bootstrap-table/dist/extensions/export/bootstrap-table-export.min',
        'bootstrap-select': '../libs/bootstrap-select/dist/js/bootstrap-select.min',
        'bootstrap-table-lang': '../libs/bootstrap-table/dist/locale/bootstrap-table-zh-CN',
        'bootstrap-slider': '../libs/bootstrap-slider/bootstrap-slider',
        'datetimepicker': '../libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min',
        'datetimepicker-lang': '../libs/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN',
        'selectpage': '../libs/fastadmin-selectpage/selectpage',
        'citypicker': '../libs/fastadmin-citypicker/dist/js/city-picker.min',
        'citypicker-data': '../libs/fastadmin-citypicker/dist/js/city-picker.data',
        'treegrid': '../libs/jquery-treegrid/js/jquery.treegrid',
        'tableexport': '../libs/tableExport.jquery.plugin/tableExport.min',
        'dragsort': '../libs/fastadmin-dragsort/jquery.dragsort',
        'bootstrap-switch': '../libs/bootstrap-switch/dist/js/bootstrap-switch',
        'slimscroll': '../libs/jquery-slimscroll/jquery.slimscroll',
        'angular': '../libs/angular/angular.min',
        'sortable': '../libs/Sortable/Sortable.min',
        'jstree': '../libs/jstree/dist/jstree.min',
        'ztree': '../libs/zTree/js/jquery.ztree.all',
        'cxselect': '../libs/fastadmin-cxselect/js/jquery.cxselect',
        'masonry': '../libs/masonry-layout/dist/masonry.pkgd',
        'bootstrap-datetimepicker': '../libs/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min',
        'bootstrap-daterangepicker': '../libs/bootstrap-daterangepicker/daterangepicker',
        'bootstrap-treegrid': '../libs/bootstrap-table/dist/extensions/treegrid/bootstrap-table-treegrid',
    },
    // shim依赖配置
    shim: {
        'addons': ['backend','trade'],
        'bootstrap': ['jquery'],
        'validator-lang': ['validator-core'],
        'qtip2': ['css!../libs/qtip2/jquery.qtip.css',],
        'plupload': {
            deps: ['../libs/plupload/js/moxie.min'],
            exports: "plupload"
        },
        'slimscroll': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'masonry-layout': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'bootstrap-table': {
            deps: [
                'bootstrap',
            ],
            exports: '$.fn.bootstrapTable'
        },
        'bootstrap-table-commonsearch': {
            deps: ['bootstrap-table'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-table-template': {
            deps: ['bootstrap-table', 'template'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-datetimepicker': [
            'moment/locale/zh-cn',
        ],
        'bootstrap-table-lang': {
            deps: ['bootstrap-table'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-table-export': {
            deps: ['bootstrap-table', 'tableexport'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-select-lang': ['bootstrap-select'],

        'bootstrap-treegrid': {
            deps: ['bootstrap-table', 'treegrid'],
            exports: '$.fn.bootstrapTable.defaults'
        },
        'bootstrap-switch': {
            deps: [
                'bootstrap',
            ],
        },
        'citypicker': ['citypicker-data', 'css!../libs/fastadmin-citypicker/dist/css/city-picker.css'],
        'ztree': {
            deps: [
                'css!../libs/zTree/css/zTreeStyle/zTreeStyle.css',
            ]
        },
        'datetimepicker-lang': [
            'datetimepicker',
        ],
        'angular': {
            exports: 'angular'
        },

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
    paths['trade/'] = 'trade/';
    require.config({paths: paths});

    // 初始化
    $(function () {
        require(['fast','backend', 'trade','cosmetic'], function (Fast,Backend, Trade, undefined) {
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
