define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                var options = {
                    extend: {
                        index_url: 'dlanguage/index',
                        del_url: 'dlanguage/del',
                        add_url: 'dlanguage/add',
                        table: 'dlanguage',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'dlanguage/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "dlanguage/index",dataType: 'json',
                    data:{
                        custom: {"dlanguage.id":$scope.row.id}
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
                        Controller.bindevent($scope);
                    });
                });
            });
        },
        bindevent:function($scope) {


            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },
        scenery: {

            codesegment:function($scope, $compile,$timeout, data){
                Table.api.init({
                    extend: {
                        del_url: 'codesegment/del',
                        add_url: 'codesegment/add?dlanguage_model_id=' + $scope.row.id,
                        table: 'codesegment',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'codesegment/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                var dataTable = $("#table-codesegment");

                $scope.$broadcast("shownTable");
            },
        },

        api: {
        }

    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});