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
                    index_url: 'principalclass/index',
                    add_url: 'principalclass/add',
                    del_url: 'principalclass/del',
                    multi_url: 'principalclass/multi',
                    summation_url: 'principalclass/summation',
                    table: 'principalclass',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'principalclass/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "principalclass/index",dataType: 'json',
                    data:{
                        custom: {"principalclass.id":$scope.row.id}
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


        },

        bindevent:function($scope){
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },

        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "principalclass/statistic",dataType: 'json',cache: false,
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