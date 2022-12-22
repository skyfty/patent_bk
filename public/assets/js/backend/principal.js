define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        //for index
        lands:{
            index:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {};
                    return param;
                };

                var options = {
                    extend: {
                        index_url: 'principal/index',
                        add_url: 'principal/add',
                        del_url: 'principal/del',
                        multi_url: 'principal/multi',
                        summation_url: 'principal/summation',
                        table: 'principal',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'principal/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },

        add: function () {
            var self = this;
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.row = {};
                $scope.row['creator_model_id'] = $scope.row['owners_model_id'] = Config.admin_id;
                $scope.row['branch_model_id'] = Config.admin_branch_model_id!= null?Config.admin_branch_model_id:0;

                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope)); $timeout(function(){ self.bindevent($scope, $timeout);  });
                });
            });
        },

        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "principal/index",dataType: 'json',
                    data:{
                        custom: {"principal.id":$scope.row.id}
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
                        "reckon_type":"principal",
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
                        summation_url: 'account/summation/reckon_type/principal',
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

            quarters:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {principal_model_id:$scope.row.id};
                    return param;
                };

                Table.api.init({
                    extend: {
                        index_url: 'quarters/index',
                        del_url: 'quarters/del',
                        summation_url: 'quarters/summation',
                        table: 'quarters',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'quarters/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            claim:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {principal_model_id:$scope.row.id};
                    return param;
                };

                Table.api.init({
                    extend: {
                        index_url: 'claim/index',
                        del_url: 'claim/del',
                        summation_url: 'claim/summation',
                        table: 'claim',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'claim/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            actualize:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {principal_model_id:$scope.row.id};
                    return param;
                };

                $scope.match = function() {
                    Fast.api.ajax({
                        url: "principal/match",
                        data: {ids:  $scope.row.id}
                    }, function () {
                        $("#table-actualize").bootstrapTable('refresh');
                        return false;
                    });
                };

                Table.api.init({
                    extend: {
                        index_url: 'actualize/index',
                        del_url: '',
                        summation_url: 'actualize/summation',
                        table: 'actualize',
                    },
                    buttons : [
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
        },

        bindevent:function($scope){

            $('[name="row[customer_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {"branch_model_id":$scope.row.branch_model_id};
                return param;
            });

            var customer_model_id = Fast.api.query("customer_model_id");
            if (customer_model_id) {
                $('[name="row[customer_model_id]"]').attr("disabled","disabled").val($scope.row['customer_model_id'] = customer_model_id);
            }

            Form.api.bindevent($("form[role=form]"), $scope.submit);
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },

        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "principal/statistic",dataType: 'json',cache: false,
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