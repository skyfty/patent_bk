define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        //for index
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        indexscape:function($scope, $compile,$timeout){
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
                    index_url: 'customer/index',
                    add_url: 'customer/add',
                    del_url: 'customer/del',
                    multi_url: 'customer/multi',
                    summation_url: 'customer/summation',
                    table: 'customer',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'customer/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "customer/index",dataType: 'json',
                    data:{
                        custom: {"customer.id":$scope.row.id}
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
        },
        scenery: {
            account:function($scope, $compile,$timeout, data){
                $scope.reckonIds = [];
                $scope.wecont = true;
                $scope.$watch("wecont", function(n,o){
                    if (n!=o) {
                        $scope.$broadcast("refurbish");
                    }
                });

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
                        "reckon_type":"customer",
                        "reckon_model_id":$scope.row.id,
                    };
                    if ($scope.reckonIds.length > 0) {
                        param.custom['cheque_model_id'] = ["in",$scope.reckonIds];
                    }
                    var types = $("#type").val();
                    if (types) {
                        param.custom['type'] = ["in",types];
                    }

                    if (!$scope.wecont) {
                        param.custom['weshow'] = 1;
                    }
                    return param;
                };

                Table.api.init({
                    extend: {
                        index_url: 'account/index',
                        multi_url: 'account/multi',
                        summation_url: 'account/summation/reckon_type/customer',
                        table: 'account',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
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
                table.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $('a[data-field="weshow"]', table).data("success", function(){
                        $scope.refreshRow();
                    });
                });

                var refresh = function(){
                    $scope.refreshRow();
                };
                $(".btn-add-account").data("callback", refresh);$(".btn-refresh").click(refresh);

                require(['bootstrap-select', 'bootstrap-select-lang'], function () {
                    $('.selectpicker').selectpicker().on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                        $scope.$broadcast("refurbish");
                    }).selectpicker('val', "main");;
                });
            },

            provider:function($scope, $compile,$timeout, data){
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

                $scope.searchFieldsParams = function(params) {
                    params.custom = {
                        'customer_model_id':$scope.row.id
                    };
                    if ($scope.genreModelIds.length > 0) {
                        params.custom['genre_cascader_id'] = ["in",$scope.genreModelIds];
                    }
                    return params;
                };

                $scope.accomplish = function() {
                    var that = this;
                    var ids = Table.api.selectedids(dataTable);
                    Layer.confirm(
                        __('确认要完成 %s 个课程订单吗?', ids.length), {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function (index) {
                            Table.api.multi("accomplish", ids, dataTable, that);
                            Layer.close(index);
                        }
                    );
                };

                Table.api.init({
                    extend: {
                        summation_url: 'provider/summation',
                        signin_url:'provider/signin',
                        accomplish_url:'provider/accomplish',
                        table: 'provider',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'provider/hinder'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                var dataTable = $("#table-provider");

                $scope.$broadcast("shownTable");
            },

        },

        bindevent:function($scope){

            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },

        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "customer/statistic",dataType: 'json',cache: false,
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