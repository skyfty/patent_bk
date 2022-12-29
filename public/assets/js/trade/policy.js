define(['jquery', 'bootstrap', 'trade','form', 'table'], function ($, undefined, Trade, Form, Table) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({

                showToggle: false,
                showColumns: false,
                showExport: false,
                commonSearch: false, //是否启用通用搜索
                extend: {
                    index_url: 'policy/index',
                    add_url: 'policy/add',
                    edit_url: 'policy/edit',
                    del_url: 'policy/del',
                    multi_url: 'policy/multi',
                    summation_url: 'policy/summation',
                    table: 'policy',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


        },


        add: function () {
            Controller.api.assignEditView("add");
        },

        edit: function () {
            Controller.api.assignEditView("edit");
        },

        api: {
        },
        init: function () {
        },
    };
    Controller.api = $.extend(Controller.api, Trade.api);
    Controller.init();

    return Controller;
});