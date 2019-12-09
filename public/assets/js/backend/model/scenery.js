define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'model/scenery/index/model_id/' + Config.model_id,
                    add_url: 'model/scenery/add/model_id/' + Config.model_id,
                    edit_url: 'model/scenery/edit/model_id/' + Config.model_id,
                    del_url: 'model/scenery/del/model_id/' + Config.model_id,
                    multi_url: 'model/scenery/multi/model_id/' + Config.model_id,
                    dragsort_url: 'model/scenery/weigh/model_id/' + Config.model_id,
                    table: 'scenery',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                sortOrder: 'ASC',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'title', title: __('Title')},
                        {field: 'pos', title: __('Pos'), formatter: function (value, row, index) {
                            return '<div class="tdtitle">' + row.pos_text + '</div>';
                        }},
                        {field: 'type', title: __('type'), formatter: function (value, row, index) {
                                return '<div class="tdtitle">' + row.type_text + '</div>';
                            }},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), visible: false, operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), visible: false, operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
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
                                        return row.title + j.text;
                                    },
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    icon: 'fa fa-list',
                                    url: 'model/sight/index/scenery_id/{ids}'
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var posStr = $(this).attr("href").replace('#','');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    if (!params.custom) {
                        params.custom = {};
                    }
                    if (posStr != "all") {
                        params.custom['pos']= posStr;
                    }
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

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
                //不可见的元素不验证
                $("form#add-form").data("validator-options", {ignore: ':hidden'});



                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});