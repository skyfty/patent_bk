define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {};

                    var branchSelect = $('[name="branch_select"]');
                    if (branchSelect.data("selectpicker")) {
                        var branchIds = branchSelect.selectpicker('val');
                        if (branchIds && branchIds.length > 0) {
                            param.custom['branch_model_id'] = ["in", branchIds];
                        }
                    }
                    return param;
                };

                var options = {
                    extend: {
                        index_url: 'staff/index',
                        add_url: 'staff/add',
                        del_url: 'staff/del',
                        summation_url: 'staff/summation',
                        table: 'staff',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'staff/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "staff/index",dataType: 'json',
                    data:{
                        custom:{id:$scope.row.id}
                    },
                    success: function (data) {
                        if (data && data.rows && data.rows.length == 1) {
                            $scope.$apply(function(){
                                $parse("row").assign($scope, data.rows[0]);
                            });
                        }
                    }
                });
            };

            $scope.changepass = function() {
                var url = "/staff/changepass?ids=" + $scope.row.id;
                Backend.api.open(url, "重置密码");
            }
        },
        scenery: {
            group:function($scope, $compile,$timeout, data){
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));

                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        add_url: 'group/infix',
                        index_url: 'group/index',
                        del_url: 'group/uninfix/model_type/staff/model_id/' + $scope.row.id,
                        table: 'group',
                    }
                });
                var table = $("#table");

                var tableOptions = {
                    toolbar: "#toolbar-group",
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'title', title: '组名', align: 'left'},
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ],
                    queryParams: function (params) {
                        params.filter = {
                            'type':'fixed',
                            'model_type':'staff',
                            'content':$scope.row.id,
                        };
                        params.filter = JSON.stringify(params.filter?params.filter:{});

                        params.op = {
                            'content':'FIND_IN_SET',
                        };
                        params.op = JSON.stringify(params.op?params.op:{});
                        return params;
                    },
                    search: false, //是否启用快速搜索
                    commonSearch: false, //是否启用通用搜索
                    showExport: false,

                };
                // 初始化表格
                table.bootstrapTable(tableOptions);
                // 为表格绑定事件
                Table.api.bindevent(table);

            },

            account: function($scope, $compile,$timeout, data){
                $scope.reckonIds = [];

                $scope.chequeChanged = function(data) {
                    var reckonIds = [];
                    angular.forEach(data.selected, function(id){
                        if ($.isNumeric(id))
                            reckonIds.push(id);
                    });
                    $scope.reckonIds = reckonIds;
                    $scope.$broadcast("refurbish");
                };

                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                        "reckon_type":"staff",
                        "reckon_model_id":$scope.row.id,
                    };
                    if ($scope.reckonIds.length > 0) {
                        param.custom['cheque_model_id'] = ["in",$scope.reckonIds];
                    }
                    return param;
                };

                Table.api.init({
                    extend: {
                        index_url: 'account/index',
                        summation_url: 'account/summation/reckon_type/staff',
                        table: 'account',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'account/view'
                        }
                    ]
                });

                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
                var table = $("#table-account");

                var refresh = function(){
                    $scope.refreshRow();
                };
                $(".btn-add-account").data("callback", refresh);$(".btn-refresh").click(refresh)

                $scope.settle = function() {
                    var ids = Table.api.selectedids(table);
                    Layer.confirm(
                        "确实要结算这些账目吗",
                        {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function (index) {
                            Fast.api.ajax({
                                url:"/account/settle",
                                data:{
                                    ids:ids,
                                    staff_model_id:$scope.row.id,
                                    branch_model_id:$scope.row.branch_model_id
                                }
                            }, function(){
                                $scope.$broadcast("refurbish");
                            });
                            Layer.close(index);
                        }
                    );
                }
            },
            provider: function($scope, $compile,$timeout, data){
                $scope.genreModelIds = [];
                $scope.classChanged = function(data) {
                    var typeIds = [];
                    angular.forEach(data.selected, function(id){
                        if ($.isNumeric(id))
                            typeIds.push(id);
                    });
                    $scope.genreModelIds = typeIds;
                    $scope.$broadcast("refurbish");
                };

                $scope.searchFieldsParams = function(param) {
                    param.custom = {staff_model_id:$scope.row.id};
                    if ($scope.genreModelIds.length > 0) {
                        param.custom['genre_cascader_id'] = ["in",$scope.genreModelIds];
                    }
                    return param;
                };

                Table.api.init({
                    extend: {
                        index_url: 'provider/index',
                        summation_url: 'provider/summation',
                        table: 'provider',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'provider/hinder'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },

        },

        refeshQuarters:function($scope, branch_id){
            var url = "auth/group/groupdata?branch_id=" + branch_id;
            $.ajax({
                type : 'get',
                dataType : 'json',
                url : url,
                success : function(datas) {
                    var groupInput = $("select[name='row[quarters][]']");
                    $("option", groupInput).remove();

                    $.each(datas.data, function(k,v){
                        var opt = $("<option>");
                        opt.val(k).html(v);
                        groupInput.append(opt);
                    });

                    if (groupInput.data("selectpicker")) {
                        if ($scope.row.quarters) {
                            groupInput.selectpicker('val', $scope.row.quarters.split(","));
                        }
                        groupInput.selectpicker('refresh');
                    }
                }
            });
        },

        bindevent:function($scope){
            var staff_group = $('[name="row[group_model_id]"]');
            staff_group.data("e-params",function(){
                var param = {
                    "model_type": "staff"
                };
                param.custom = {
                    "model_type": "staff",
                    "type": "fixed"
                };
                return param;
            }).data("source", "group/index").data("search-field","title").data("show-field","title").data("order-by",['title']);

            Form.api.bindevent($("form[role=form]"), $scope.submit);

            $('[name="row[branch_model_id]"]').data("e-selected", function(data){
                Controller.refeshQuarters($scope, data.id);
            });
            if (Config.admin_branch_model_id)$('[data-field-name="branch"]').hide().trigger("rate");
        },

        changepass:function() {
            Form.api.bindevent($("form[role=form]"),function(){
                Backend.api.close();
            });
        },
        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "staff/statistic",dataType: 'json',cache: false,
                        success: function (ret) {
                            $scope.$apply(function(){
                                $scope.stat = ret.data;
                            });
                        }
                    });
                };
                $scope.$on("refurbish", $scope.refresh);$scope.refresh(); $(".btn-refresh").on("click", $scope.refresh);
            });
        },
        api: {

        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});