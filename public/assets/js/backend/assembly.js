define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        indexscape:function($scope, $compile,$timeout){
            var options = {
                extend: {
                    index_url: 'assembly/index',
                    add_url: 'assembly/add',
                    del_url: 'assembly/del',
                    summation_url: 'assembly/summation',
                    table: 'assembly',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __('%s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'assembly/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "assembly/index",dataType: 'json',
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
            behavior:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "assembly_model_id": $scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    extend: {
                        del_url: 'behavior/del',
                        summation_url: 'behavior/summation',
                        table: 'behavior',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic  btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'behavior/view'
                        }
                    ]

                });
                $scope.fields = data.fields;
                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            }
        },

        add: function () {
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.row = {};
                $scope.row['creator_model_id'] = $scope.row['owners_model_id'] = Config.admin_id;
                $scope.row['branch_model_id'] = Config.staff?Config.staff.branch_model_id:0;

                $scope.submit = function(data, ret){
                    Backend.api.addtabs("assembly/view/ids/" + data.id, __('%s',data.idcode));
                    Backend.api.close();
                };
                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope));
                    $timeout(function(){
                        var warehouse_model_id = Fast.api.query("warehouse_model_id");
                        if (warehouse_model_id) {
                            $('[name="row[warehouse_model_id]"]').val($scope.row['warehouse_model_id'] = warehouse_model_id);
                        }
                        Controller.bindevent($scope);
                    });
                });
            });
        },
        bindevent:function($scope) {
            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },
        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});
