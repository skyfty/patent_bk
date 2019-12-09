define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'model/fields/index/model_id/' + Config.modelx.id,
                    add_url: 'model/fields/add/model_id/' + Config.modelx.id + '/model_table/' + Config.modelx.table ,
                    edit_url: 'model/fields/edit/model_id/' + Config.modelx.id + '/model_table/' + Config.modelx.table ,
                    del_url: 'model/fields/del/model_id/' + Config.modelx.id,
                    multi_url: 'model/fields/multi/model_id/' + Config.modelx.id,
                    table: 'fields',
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
                        {
                            checkbox: true
                        },
                        {field: 'id', sortable: true, title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'type', title: __('Type')},
                        {field: 'title', title: __('Title')},
                        {field: 'defaultvalue', title: "默认值"},
                        {field: 'rule_text', title: "规则"},
                        {field: 'createtime', title: __('Createtime'), visible: false, operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'newstatus', title: __('NewStatus'), formatter: Table.api.formatter.status},
                        {field: 'editstatus', title: __('EditStatus'), formatter: Table.api.formatter.status},
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
            Controller.api.bindevent("add");

            $('[name="secenery"]').data('format-item', function(row){
                return row.title + "," + row.pos;
            }).data('params', function(row){
                return {custom:{
                    type:'default'
                }};
            });
        },
        edit: function () {
            Controller.api.bindevent("edit");
        },
        api: {
            bindevent: function (e) {
                //不可见的元素不验证
                $("form#add-form").data("validator-options", {ignore: ':hidden'});
                $(document).on("change", "#c-relevance", function () {
                    var val = $(this).val();
                    if (val != "") {
                        $("#c-length").val(10).attr("disabled", "disabled");
                        $("#c-type").attr("disabled", "disabled");
                        $("#c-rule").attr("disabled", "disabled");
                        $("#c-content").attr("disabled", "disabled");
                        $("#c-defaultvalue").attr("disabled", "disabled");
                    } else {
                        $("#c-type").removeAttr("disabled");;
                        $("#c-length").removeAttr("disabled");;
                        $("#c-rule").removeAttr("disabled");;
                        $("#c-content").removeAttr("disabled");;
                        $("#c-defaultvalue").removeAttr("disabled");;
                    }
                });
                if (e == "edit") {
                    $("#c-relevance").trigger("change");
                }

                $(document).on("change", "#c-type", function () {
                    $(".tf").addClass("hidden");
                    $(".tf.tf-" + $(this).val()).removeClass("hidden");

                    var type = $(this).val();
                    switch(type) {
                        case "number": {
                            $("#c-length").val(10).attr("readonly", "readonly");
                            $("#c-defaultvalue").val(0);
                            $("#c-content").removeAttr("readonly").val("");;
                            break;
                        }
                        case "date":
                        case "time":
                        case "datetime":{
                            $("#c-length").val(10).attr("readonly", "readonly");
                            $("#c-defaultvalue").attr("readonly", "readonly").val("0");;
                            break;
                        }
                        case "image":
                        case "images":
                        case "file":
                        case "files":{
                            $("#c-length").val(255).attr("readonly", "readonly");
                            $("#c-defaultvalue").attr("readonly", "readonly").val("");;
                            break;
                        }
                        case "address": {
                            $("#c-length").val(255).attr("readonly", "readonly");
                            $("#c-defaultvalue").attr("readonly", "readonly").val("");;
                            break;
                        }
                        case "model": {
                            $("#c-length").attr("readonly", "readonly");
                            if (e == "edit") {
                                $("#c-defaultvalue").attr("readonly", "readonly");
                            } else {
                                $("#c-defaultvalue").removeAttr("readonly").val("");;
                            }
                            break;
                        }
                        default: {
                            $("#c-length").val(255).removeAttr("readonly");
                            $("#c-defaultvalue").removeAttr("readonly");
                        }
                    }
                });
                Form.api.bindevent($("form[role=form]"));
                $("#c-type").trigger("change");

            }
        }
    };
    return Controller;
});