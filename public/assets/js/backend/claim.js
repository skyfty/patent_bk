define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        indexscape:function($scope, $compile,$timeout){
            var options = {
                extend: {
                    index_url: 'claim/index',
                    del_url: 'claim/del',
                    add_url: 'claim/add',
                    table: 'claim',
                }
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },

        add: function () {
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.row = {};
                $scope.row['owners_model_id'] = Config.admin_id;
                $scope.row['branch_model_id'] = Config.staff?Config.staff.branch_model_id:0;

                $scope.submit = function(data, ret){
                    Backend.api.close();
                };
                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope));
                    $timeout(function(){
                        var principal_model_id = Fast.api.query("principal_model_id");
                        if (principal_model_id) {
                            $('[name="row[principal_model_id]"]').attr("disabled","disabled").val($scope.row['principal_model_id'] = principal_model_id);
                        }
                        var customer_model_id = Fast.api.query("customer_model_id");
                        if (customer_model_id) {
                            $('[name="row[customer_model_id]"]').attr("disabled","disabled").val($scope.row['customer_model_id'] = customer_model_id);
                        }
                        Controller.bindevent($scope);
                    });
                });
            });
        },
        bindevent:function($scope) {
            if (customer) {
                $('[name="row[principal_model_id]"]').data("e-params",function(){
                    var param = {"branch_model_id":customer.branch_model_id};
                    return param;
                });
            }
            if (genearch) {
                $('[name="row[customer_model_id]"]').data("e-params", function () {
                    var param = {"branch_model_id":genearch.branch_model_id};
                    return param;
                });
            }
            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },
        scenery: {
        },

        api: {
        }

    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});