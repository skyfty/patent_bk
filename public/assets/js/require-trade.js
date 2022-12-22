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
        'bootstrap-table': '../libs/bootstrap-table/dist/bootstrap-table.min',
        'bootstrap-select-lang': '../libs/bootstrap-select/dist/js/i18n/defaults-zh_CN',
        'bootstrap-table-export': '../libs/bootstrap-table/dist/extensions/export/bootstrap-table-export.min',
        'bootstrap-select': '../libs/bootstrap-select/dist/js/bootstrap-select.min',
        'bootstrap-table-lang': '../libs/bootstrap-table/dist/locale/bootstrap-table-zh-CN',
        'bootstrap-slider': '../libs/bootstrap-slider/bootstrap-slider',
        'bootstrap-treegrid': '../libs/bootstrap-table/dist/extensions/treegrid/bootstrap-table-treegrid',
        'treegrid': '../libs/jquery-treegrid/js/jquery.treegrid',
        'tableexport': '../libs/tableExport.jquery.plugin/tableExport.min',
        'dragsort': '../libs/fastadmin-dragsort/jquery.dragsort',

        'bootstrap-switch': '../libs/bootstrap-switch/dist/js/bootstrap-switch',

    },
    // shim依赖配置
    shim: {
        'addons': ['trade'],
        'bootstrap': ['jquery'],
        'validator-lang': ['validator-core'],
        'qtip2': ['css!../libs/qtip2/jquery.qtip.css',],
        'plupload': {
            deps: ['../libs/plupload/js/moxie.min'],
            exports: "plupload"
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
        require(['fast', 'trade'], function (Fast, Trade) {
            //加载相应模块
            if (Config.jsname) {
                require([Config.jsname], function (Controller) {
                    //创建angularJS模块
                    if (Controller[Config.actionname]) {
                        Controller[Config.actionname]();
                    } else if (Controller["defaultAction"]) {
                        Controller["defaultAction"]();
                    }
                }, function (e) {
                    console.error(e);
                });
            }
        });
    });
});
