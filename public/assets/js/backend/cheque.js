define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cheque/index',
                    add_url: 'cheque/add',
                    edit_url: 'cheque/edit',
                    del_url: 'cheque/del',
                    multi_url: 'cheque/multi',
                    summation_url: 'cheque/summation',
                    table: 'cheque',
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
                        {field: 'reckon_text', title: __('Model_id')},
                        {field: 'name', title: __('Name')},
                        {field: 'description', title: __('Description')},
                        {field: 'mold_text', title: __('Type'), operate: false},
                        {field: 'related_text', title: __('Related_model')},
                        {field: 'inflow_text', title: __('Inflow_model')},
                        {field: 'inflow_model_id_text', title: __('Inflow_model_id')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var reckonId = $(this).attr("href").replace('#','');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    if (reckonId != "all") {
                        params.custom = {'reckon_id':reckonId};
                    }
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                var eParams ={custom:{}};

   
                var inflow = $("#c-inflow_model");
                inflow.data("e-selected", function (data) {
                    var mold = $('[name="row[mold]"]').val();
                    eParams.custom ={mold:-mold, reckon_id:data.id};
                    $('#c-inflow_model_id').selectPageClear();
                });
                eParams.custom.reckon_id = inflow.val();

                var rowMold = $('[name="row[mold]"]');
                rowMold.change(function () {
                    var mold = $(this).val();
                    eParams.custom.mold = -mold;
                    $('#c-inflow_model_id').selectPageClear();
                });
                eParams.custom.mold = -1 * rowMold.val();

                $("#c-inflow_model_id").data("e-params", function (data) {
                    return eParams;
                });

                $("#c-reckon_id").data("e-params", function () {
                    var param = {
                        custom:{
                            "accountswitch":1
                        }
                    };
                    return param;
                });

                $("#c-inflow_model").data("e-params", function () {
                    var param = {
                        custom:{
                            "accountswitch":1
                        }
                    };
                    return param;
                });
                $("#c-related_model").data("e-params", function () {
                    var param = {
                        custom:{
                            "accountswitch":1
                        }
                    };
                    return param;
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    Controller.api = $.extend(Backend.api, Controller.api);
    return Controller;
});