define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'model/modelx/index',
                    add_url: 'model/modelx/add',
                    edit_url: 'model/modelx/edit',
                    del_url: 'model/modelx/del',
                    multi_url: 'model/modelx/multi',
                    table: 'model',
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
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'table', title: __('Table')},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
                        {
                            field: 'createtime',
                            sortable: true,
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime',
                            sortable: true,
                            title: __('Updatetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'fields',
                                    text: __('Fields'),
                                    title: function(row, j){
                                        return row.name + j.text;
                                    },
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    icon: 'fa fa-list',
                                    url: 'model/fields/index/model_id/{ids}'
                                },
                                {
                                    name: 'scenery',
                                    text: __('Scenery'),
                                    title: function(row, j){
                                        return row.name + j.text;
                                    },
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    icon: 'fa fa-list',
                                    url: 'model/scenery/index/model_id/{ids}'
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
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
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});