define(['jquery', 'bootstrap', 'backend', 'table', 'form','bootstrap-treegrid'], function ($, undefined, Backend, Table, Form,undefinded) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'genre/index',
                    add_url: 'genre/add',
                    edit_url: 'genre/edit',
                    del_url: 'genre/del',
                    table: 'genre',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                escape: false,
                sortName: 'id',
                pagination: false,
                commonSearch: false,
                columns: [
                    [
                        {checkbox: true},
                        {
                            field: 'name', title: __('Name'), align: 'left'
                        },
                        {
                            field: 'order', title: "顺序", align: 'left'
                        },
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                treeShowField:"name",
                parentIdField:"pid",
                onLoadSuccess:function (data) {
                    table.treegrid({
                        treeColumn: 1,
                        onChange: function () {
                            table.bootstrapTable('resetWidth');
                        }
                    });
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});