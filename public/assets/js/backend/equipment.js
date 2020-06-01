define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
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
                    index_url: 'equipment/index',
                    add_url: 'equipment/add',
                    del_url: 'equipment/del',
                    multi_url: 'equipment/multi',
                    summation_url: 'equipment/summation',
                    table: 'equipment',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __('%s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'equipment/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "equipment/index",dataType: 'json',
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
        },
        scenery: {
            assembly:function($scope, $compile,$timeout, data){
                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                        "warehouse_model_id":$scope.row.warehouse_id,
                    };
                    return param;
                };

                Table.api.init({
                    extend: {
                        del_url: 'assembly/del',
                        summation_url: 'assembly/summation',
                        table: 'assembly',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'assembly/hinder'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
        },

        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "equipment/statistic",dataType: 'json',cache: false,
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
        add: function () {
            var self = this;
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.row = {};
                var branch_model_id = Backend.api.query("branch_model_id");
                if (branch_model_id) {
                    $scope.row['branch_model_id'] = branch_model_id;
                } else{
                    $scope.row['branch_model_id'] = Config.admin_branch_model_id!= null?Config.admin_branch_model_id:0;
                }
                var classroom_model_id = Backend.api.query("classroom_model_id");
                if (classroom_model_id) {
                    $scope.row['classroom_model_id'] = classroom_model_id;
                }
                $scope.row['creator_model_id'] = $scope.row['owners_model_id'] = Config.admin_id;
                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope)); $timeout(function(){ self.bindevent($scope, $timeout);  });
                });
            });
        },
        bindevent:function($scope,$timeout){
            $('[name="row[classroom_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom ={};
                if (typeof $scope.row.branch_model_id != "undefined") {
                    param.custom["branch_model_id"] = $scope.row.branch_model_id;
                }
                return param;
            });
            $('[name="row[branch_model_id]"]').data("e-selected", function(data){
                $('[name="row[classroom_model_id]"]').selectPageClear();
            });

            $('[name="row[arrange_model_id]"]').data("e-params",function(){
                var param = {custom:{}};
                var depot = Backend.api.query("depot");
                if (depot) {
                    param.custom['depot'] = depot;
                }
                return param;
            });

            Form.api.bindevent($("form[role=form]"), function(data, ret){
                Backend.api.close(ret);
            });
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },
        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});