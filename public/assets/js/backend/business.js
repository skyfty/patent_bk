define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic', 'layer'], function ($, Backend, Table, Form, Template,angular, Cosmetic, Layer) {
    var Controller = {
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
                    index_url: 'business/index',
                    add_url: 'business/add',
                    del_url: 'business/del',
                    multi_url: 'business/multi',
                    summation_url: 'business/summation',
                    table: 'business',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __('%s', row.idcode);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'business/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },

        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                var defer = $.Deferred();
                $.ajax({url: "business/index",dataType: 'json',
                    data:{
                        custom:{
                            'business.id':$scope.row.id
                        }
                    },
                    success: function (data) {
                        if (data && data.rows && data.rows.length == 1) {
                            $scope.$apply(function(){
                                $parse("row").assign($scope, data.rows[0]);
                            });
                            $scope.refreshState();
                            defer.resolve(data.rows);
                        }
                    }
                });
                return defer;
            };

            $scope.refreshState = function() {
                $scope.settle_state = $scope.row.settle_state == 916 && $scope.row.status != 'locked';
            };
            $scope.refreshState();

            $scope.settle = function() {
                Layer.confirm( "确定要提交结算吗?",  {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                    function (index) {
                        Backend.api.ajax({url: "business/settle",data: {ids:$scope.row.id}}, function (data, ret) {
                            $scope.refreshRow();
                        });
                        Layer.close(index);
                    }
                );
            }
        },

        scenery: {
            account:function($scope, $compile,$timeout, data){
                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                        "reckon_type":"customer",
                        "related_type":"business",
                        "related_model_id":$scope.row.id,
                    };
                    return param;
                };
                Table.api.init({
                    extend: {
                        del_url: 'account/del/reckon_type/customer',
                        summation_url: 'account/summation/reckon_type/customer',
                        table: 'account',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.idcode);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'account/view'
                        }
                    ],
                    onRefresh:function(){
                    }
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");

                $(".btn-add-account").data("callback", function(){
                    $scope.refreshRow().then(function(data){
                        if ($scope.settle_state) {
                            $scope.settle();
                        }
                    });
                });
            },
        },

        initParam:[
            'customer_model_id','branch_model_id'],

        add: function () {
            var self = this;
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.pre ={}; $scope.row = {};

                $scope.row['branch_model_id'] = Config.admin_branch_model_id!= null?Config.admin_branch_model_id:0;
                $scope.row['creator_model_id'] = $scope.row['owners_model_id'] = Config.admin_id;

                for(var i in self.initParam) {
                    var param = Backend.api.query(self.initParam[i]);
                    if (param) {
                        $scope.row[self.initParam[i]] = param;
                    }
                }
                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope));
                    $timeout(function(){
                        self.bindevent($scope);
                    });
                });
            });
        },

        bindevent:function($scope) {
            var self = this;

            $('[name="row[branch_model_id]"]').data("e-selected", function(data){
                $('[name="row[presell_model_id]"]').selectPageClear();
            });

            var presell = null;
            $('[name="row[presell_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {"branch_model_id":['in',[$scope.row.branch_model_id,0]]};
                return param;
            }).data("e-selected", function(data){
                presell = data.row;
                $scope.row['sum_settle_price'] =presell['price'] * presell['times'] * $scope.row['amount'] ;
            });

            $('[name="row[customer_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {"branch_model_id":$scope.row.branch_model_id};
                return param;
            });

            $scope.$watch("row.amount", function(){
                if (presell) {
                    $scope.row['sum_settle_price'] =presell['price'] * presell['times'] * $scope.row['amount'] ;
                } else {
                    $scope.row['sum_settle_price'] = 0;
                }

            });
            Form.api.bindevent($("form[role=form]"), $scope.submit);

            require(['selectpage'], function () {
                for (var i in self.initParam) {
                    var param = Backend.api.query(self.initParam[i]);
                    if (param) {
                        $('[name="row[' + self.initParam[i] + ']"]').selectPageDisabled(true);
                    }
                }
            });
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },

        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "business/statistic",dataType: 'json',cache: false,
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