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
                return param;
            };

            $scope.detailFormater = function(idx, row) {
                if (typeof row.substance != "undefined") {
                    var field_values = [];
                    for (var idx in row.substance_fields) {
                        var field = row.substance_fields[idx];
                        var data = row['substance'][field.name];
                        var html = Cosmetic.api.formatter(field, data, row['substance']);
                        field_values.push(field.title + " : " + html)
                    }
                    return field_values.join("<br/>");
                }
                return "没有关联";
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
            substance:function($scope, $compile,$timeout, data) {

            }
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