define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'chequecate/index',
                    add_url: 'chequecate/add',
                    edit_url: 'chequecate/edit',
                    del_url: 'chequecate/del',
                    multi_url: 'chequecate/multi',
                    summation_url: 'chequecate/summation',
                    dragsort_url: '',
                    table: 'chequecate',
                }
            });


            var table = $("#table");
            var tableOptions = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                escape: false,
                pk: 'id',
                pagination: false,
                commonSearch: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'type', title: __('Type'),formatter: function(value, row, index){
                            value = value === null ? '' : value.toString();
                            return __(value);
                        }},
                        {field: 'name', title: __('Name'), align: 'left'},
                        {field: 'cheque_text', title: '账目列表', align: 'left'},

                        {field: 'status', title: __('Status'), operate: false, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            };
            // 初始化表格
            table.bootstrapTable(tableOptions);
            // 为表格绑定事件
            Table.api.bindevent(table);

            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var typeStr = $(this).attr("href").replace('#','');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    params.type = typeStr;
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
                $(document).on("change", "#c-type", function () {
                    $("#c-pid option[data-type='all']").prop("selected", true);
                    $("#c-pid option").removeClass("hide");
                    $("#c-pid option[data-type!='" + $(this).val() + "'][data-type!='all']").addClass("hide");
                    $("#c-pid").selectpicker("refresh");
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    Controller.api = $.extend(Backend.api, Controller.api);
    return Controller;
});