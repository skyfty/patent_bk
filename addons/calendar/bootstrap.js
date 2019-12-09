require.config({
    paths: {
        'fullcalendar': '../libs/fullcalendar/dist/fullcalendar',
        'fullcalendar-lang': '../libs/fullcalendar/dist/locale/zh-cn',
    },
    // shim依赖配置
    shim: {
        'fullcalendar-lang': ['fullcalendar'],
    },
});