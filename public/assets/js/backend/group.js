define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        queryParams: function(params) {
            params.filter = params.filter?JSON.parse(params.filter):{};

            var typeStr = $('ul.nav-tabs li.active a[data-toggle="tab"]').attr("href").replace('#','');
            var filter = {"model_type":Config.modelType};
            if (typeStr != "all") {
                filter['type'] = typeStr;
            }

            params.filter = JSON.stringify($.extend({}, params.filter,filter));
            params.searchField = ["id","title"];
            return params;
        },

        index: function () {
            var self = this;
            Table.api.init({
                extend: {
                    index_url: 'group/index?model_type=' +Config.modelType,
                    add_url: 'group/add?model_type=' +Config.modelType,
                    del_url: 'group/del?model_type=' +Config.modelType,
                    edit_url: 'group/edit?model_type=' +Config.modelType,
                    multi_url: 'group/multi?model_type=' +Config.modelType,
                    rule_url: 'group/rule?model_type=' +Config.modelType,
                    table: 'group',
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
                            field: 'title', title: __('Title'), align: 'left',
                            custom:{fixed: 'success', cond: 'info'},
                            formatter: function (value, row, index) {
                                return  Table.api.formatter.flag.call(this, row['type'], row, index) + " " + value;
                            }
                        },
                        {
                            field: 'branch_name', title: "校区", align: 'left',
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
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
                                name: 'changerule',
                                text: __('Change rule'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url:  $.fn.bootstrapTable.defaults.extend.rule_url,
                            }],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
                queryParams:self.queryParams
            });
            // 为表格绑定事件
            Table.api.bindevent(table);

            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                table.bootstrapTable('refresh', {});
                return false;
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        infix: function () {
            $('.selectpage').data("e-params",function(){
                var param = {
                    custom:{
                        'model_type':$("[name='model_type']").val(),
                        'type':'fixed'
                    },
                };
                return param;
            });
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        rule: function () {
            AngularApp.controller("cond", function($scope,$sce, $compile,$timeout) {
                $scope.allFields = Config.fields;

                $scope.searchFields = [];
                angular.forEach($scope.allFields, function(f){
                    if ($.inArray(f.type, ['image','images','file','files']) == -1) {
                        $scope.searchFields.push(f);
                    }
                });
                var content = [];
                var ruleContent = Config.row.content;
                for(var x in ruleContent) {
                    for (var f in $scope.allFields) {
                        if ($scope.allFields[f].id == ruleContent[x].field) {
                            var vc = ruleContent[x];
                            content.push({
                                condition:vc.condition,
                                value:vc.value,
                                field:$scope.allFields[f]
                            });
                            break;
                        }
                    }
                }
                $scope.condContent = content;

                $scope.searchFields.splice(0, 0, Cosmetic.config.defaultsearchfield);
                $scope.submit = function(id){
                    var content = [];
                    var fieldIds = [];
                    angular.forEach($scope.condContent, function(v){
                        if ($.inArray(v.field.id, fieldIds) == -1) {
                            fieldIds.push(v.field.id);
                            content.push({condition: v.condition, value: v.value, field: v.field.id});
                        }
                    });
                    Fast.api.close({url: 'group/rule'});
                    var params = {
                        url: 'group/rule',
                        data: {
                            type: "cond",
                            ids:id,
                            content:content
                        }
                    };
                    Fast.api.ajax(params);
                };
                $timeout(function(){Controller.api.bindevent();},0);
            });

            AngularApp.controller("fixed", function($scope) {
                $scope.ruleIds = Config.row.content?Config.row.content:[];
                var modelIndex = Config.modelType + "/index";
                Table.api.init({
                    extend: {
                        del_url: 'group/rerule/group_id/' + Config.row.id,
                        index_url: modelIndex,
                    }
                });

                var table = $("#table");

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    search:true,
                    pageSize: 7,
                    columns: [
                        [
                            {checkbox: true},
                            {
                                field: 'idcode',
                                title: __('Id')},
                            {
                                field: 'name',
                                title: __('Name'),
                                align: 'left'},
                            {
                                field: 'operate',
                                width: '130px',
                                title: __('Operate'),
                                table: table,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ],
                    queryParams: function (params) {
                        params.filter = JSON.stringify($.extend({},params.filter?JSON.parse(params.filter):{},{"id":$scope.ruleIds}));
                        params.op = JSON.stringify($.extend({},params.op?JSON.parse(params.op):{}, {"id":"in"}));
                        params.searchField = ["name"];
                        return params;
                    }
                });
                Table.api.bindevent(table);

                $scope.applyFixed = function(id) {
                    var content = $("#ruleids").val();
                    if (content == "")
                        return;
                    $scope.ruleIds = $.unique($.merge($scope.ruleIds,content.split(',')));

                    var params = {
                        url: 'group/rule',
                        data: {
                            type: "fixed",
                            ids:id,
                            content:$scope.ruleIds
                        }
                    };
                    Fast.api.ajax(params, function (data, ret) {
                        table.bootstrapTable('refresh', {});
                        $('#ruleids').selectPageClear();
                        return false;
                    });
                };
                Controller.api.bindevent();

                $scope.rerule = function(content, ret) {
                    $scope.ruleIds = $.grep($scope.ruleIds, function( n, i ) {
                        return $.inArray(n, content ) == -1;
                    });
                    table.bootstrapTable('refresh', {});
                };

                //当内容渲染完成后
                table.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-delone").data("success", $scope.rerule);
                });
                $(".toolbar > .btn-del,.toolbar .btn-more~ul>li>a").data("success", $scope.rerule);
            });
        },

        select: function () {
            var self = this;
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'group/index?model_type=' +Config.modelType,
                }
            });

            var options = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'id',
                singleSelect:true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), visible:false},
                        {
                            field: 'title', title: __('Title'), align: 'left',
                            custom:{fixed: 'success', cond: 'info'},
                            formatter: function (value, row, index) {
                                return  Table.api.formatter.flag.call(this, row['type'], row, index) + " " + value;
                            }
                        },
                        {
                            field: 'branch_name', title: "校区", align: 'left',
                        },
                        {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {
                            field: 'operate', title: __('Operate'), events: {
                            'click .btn-chooseone': function (e, value, row, index) {
                                Fast.api.close({gs: row, multiple: false});
                            }
                        }, formatter: function () {
                            return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                        }
                        }
                    ]
                ],
                queryParams:self.queryParams
            };
            if (Fast.api.query("multiple") != "") {
                options['singleSelect'] = false;
            }
            var table = $("#table");
            table.bootstrapTable(options);
            // 为表格绑定事件
            Table.api.bindevent(table);

            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                table.bootstrapTable('refresh', {});
                return false;
            });

            $(".btn-choose-multi").on("click", function(){
                var rows = table.bootstrapTable('getSelections');
                Fast.api.close({gs: rows, multiple: true});
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                if (Config.staff != null) {
                    $('#form-branch_model_id').hide().trigger("rate");
                    $('[name="row[branch_model_id]"]').attr("disabled","disabled").val(Config.staff.branch_model_id);
                }
            }
        }
    };
    Controller.api = $.extend(Backend.api, Controller.api);
    return Controller;
});