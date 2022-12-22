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
                    index_url: 'principal/index',
                    add_url: 'principal/add',
                    edit_url: 'principal/edit',
                    del_url: 'principal/del',
                    multi_url: 'principal/multi',
                    summation_url: 'principal/summation',
                    table: 'principal',
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
            Controller.api.bindevent();

        },

        edit: function () {
            Controller.api.bindevent();

        },

        api: {
            bindevent: function () {
                Form.api.bindevent($("#form"), function(data, ret){
                    setTimeout(function(){window.location.replace(ret.url);}, 1000);
                });
            }
        }
    };

    return Controller;
});