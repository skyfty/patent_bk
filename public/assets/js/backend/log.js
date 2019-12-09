define(['jquery', 'backend', 'table', 'form'], function ($, Backend, Table, Form) {
    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'log/index/model_type/' +Config.modelType,
                    add_url: 'log/add/model_type/' +Config.modelType,
                    view_url: 'log/view/model_type/' +Config.modelType,
                    del_url: 'log/del/model_type/' +Config.modelType,
                    multi_url: 'log/multi/model_type/' +Config.modelType,
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
                        {
                            field: 'title', title: __('Title'), align: 'left', formatter: function (value, row, index) {
                                return  Table.api.formatter.flag.call(this, row['typedata'], row, index) + " " + value;
                            }
                        },
                        {
                            field: 'model_text',
                            title: __('model_text'),
                            align: 'left',
                            formatter: function (value, row, index) {
                                var url = row.model_type + "/hinder/ids/" + row.model_id;
                                var title =__("View %s", value);
                                return '<a href="' + Fast.api.fixurl(url) + '" class=" btn-detail btn-dialog" data-value="' + value + '" title="' + title + '">' + value + '</a>';
                            }
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'username', title: __('Username'), formatter: Table.api.formatter.search},

                        {

                            field: 'status',
                            title: __('Status'),
                            table: table,
                            operate: false,
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'log/view/model_type/' +Config.modelType,
                            }],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
                queryParams: function (params) {
                    params.searchField = ["id","title"];
                    params.custom = {
                        "model_type": Config.modelType,
                    };
                    return params;
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
    };
    return Controller;
});