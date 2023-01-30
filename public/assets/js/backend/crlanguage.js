define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                var options = {
                    extend: {
                        index_url: 'crlanguage/index',
                        del_url: 'crlanguage/del',
                        add_url: 'crlanguage/add',
                        table: 'crlanguage',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'crlanguage/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "crlanguage/index",dataType: 'json',
                    data:{
                        custom: {"crlanguage.id":$scope.row.id}
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
                        $timeout(function(){
                            var copyright_model_id = Fast.api.query("copyright_model_id");
                            if (copyright_model_id) {
                                $('[name="row[copyright_model_id]"]').attr("disabled","disabled").val($scope.row['copyright_model_id'] = copyright_model_id);
                            }
                            Controller.bindevent($scope);
                        });
                        Controller.bindevent($scope);
                    });
                });
            });
        },
        bindevent:function($scope) {


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