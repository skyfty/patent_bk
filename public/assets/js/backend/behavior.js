define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        indexscape:function($scope, $compile,$timeout){
            var options = {
                extend: {
                    index_url: 'behavior/index',
                    add_url: 'behavior/add',
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
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'behavior/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "behavior/index",dataType: 'json',
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

        },

        add: function () {
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.row = {};
                $scope.row['creator_model_id'] = $scope.row['owners_model_id'] = Config.admin_id;
                var branch_model_id = Backend.api.query("branch_model_id");
                if (branch_model_id) {
                    $scope.row['branch_model_id'] = branch_model_id;
                } else{
                    $scope.row['branch_model_id'] = Config.admin_branch_model_id!= null?Config.admin_branch_model_id:0;
                }
                $scope.submit = function(data, ret){
                    Backend.api.addtabs("behavior/view/ids/" + data.id, __('%s',data.idcode));
                    Backend.api.close();
                };
                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope));
                    $timeout(function(){
                        var assembly_model_id = Fast.api.query("assembly_model_id");
                        if (assembly_model_id) {
                            $('[name="row[assembly_model_id]"]').val($scope.row['assembly_model_id'] = assembly_model_id);
                        }
                        Form.api.bindevent($("form[role=form]"), $scope.submit);
                    });
                });
            });
        },

        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});