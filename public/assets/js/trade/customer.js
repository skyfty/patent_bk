define(['jquery', 'bootstrap', 'trade','form', 'table','template'], function ($, undefined, Trade, Form, Table, Template) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                showToggle: false,
                showColumns: false,
                showExport: false,
                commonSearch: false, //是否启用通用搜索
                extend: {
                    index_url: 'customer/index',
                    add_url: 'customer/add',
                    multi_url: '',
                    summation_url: '',
                    table: 'customer',
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
                        {field: 'name', title: "姓名"},
                        {field: 'telephone', title: "telephone"},
                        {field: 'sex', title: "sex"},
                        {field: 'status', title: "状态"},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: function(value, row) {
                                return Template("customer-operate-tmpl",row);
                            }
                        }
                    ]
                ],

                detailView:true,
                detailFormatter:function (index, row) {
                    return Template("customer-detail-tmpl",row);
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("#form"), function(data, ret){
                setTimeout(function(){
                    window.location.replace("/principal/add?customer_id=" + data.id);
                    }, 1000);
            });
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