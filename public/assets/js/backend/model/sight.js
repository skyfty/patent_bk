define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'model/sight/index/scenery_id/' + Config.scenery.id,
                    add_url: 'model/sight/add/scenery_id/' + Config.scenery.id + "/model_id/" + Config.scenery.model_id + "/table/" + Config.scenery.table,
                    del_url: 'model/sight/del/scenery_id/' + Config.scenery.id,
                    multi_url: 'model/sight/multi/scenery_id/' + Config.scenery.id,
                    table: 'sight',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                sortOrder: 'asc',

                columns: [
                    [
                        {checkbox: true},
                        {
                            field: 'id',
                            title: __('Id')
                        },
                        {
                            field: 'scenery.title',
                            title: __('Scenery_id'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return '<a href="javascript:;" class="searchit" data-field="scenery_id" data-value="' + row.scenery_id + '">' + value + '</a>';
                            }
                        },
                        {
                            field: 'fields.title',
                            title: __('Fields_id'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return '<a href="javascript:;" class="searchit" data-field="fields_id" data-value="' + row.fields_id + '">' + value + '</a>';
                            }
                        },
                        {
                            field: 'scenery.table',
                            title: '表',
                            operate: false
                        },
                        {
                            field: 'fields.name',
                            title: '字段名',
                            operate: false
                        },
                        {
                            field: 'fields.type',
                            title: __('Fields_type'),
                            operate: false
                        },
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
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