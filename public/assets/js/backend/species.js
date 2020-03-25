define(['jquery', 'bootstrap', 'backend', 'table', 'form','bootstrap-treegrid'], function ($, undefined, Backend, Table, Form,undefinded) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'species/index',
                    add_url: 'species/add',
                    edit_url: 'species/edit',
                    del_url: 'species/del',
                    table: 'species',
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
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name: 'view',
                                    title: function(row, j){
                                        return __('%s', row.idcode);
                                    },
                                    classname: 'btn btn-xs btn-success btn-magic btn-addtabs btn-view',
                                    icon: 'fa fa-folder-o',
                                    url: function(row){
                                        return 'procedure/index?relevance_model_type=' + row.model + "&species_cascader_id=" + row.id;
                                    }
                                }
                            ]
                        }
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