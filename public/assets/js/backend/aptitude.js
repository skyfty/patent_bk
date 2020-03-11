define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                var options = {
                    extend: {
                        index_url: 'aptitude/index',
                        add_url: 'aptitude/add',
                        del_url: 'aptitude/del',
                        summation_url: 'aptitude/summation',
                        table: 'aptitude',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'aptitude/view'
                        }
                    ]
                };
                Table.api.init(options);
                var table = $("#table-index");

                $scope.searchFieldsParams = function(param) {
                    param.custom = {};

                    return param;
                };

                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "aptitude/index",dataType: 'json',
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
            procshutter:function($scope, $compile,$timeout, data) {
                $scope.procedures = [];

                $scope.classChanged = function(data) {
                    var procedures = [];
                    angular.forEach(data.selected, function(id){
                        if ($.isNumeric(id))
                            procedures.push(id);
                    });
                    $scope.procedures = procedures;
                    $scope.$broadcast("refurbish");
                };
                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                    };

                    if ($scope.procedures.length > 0) {
                        param.custom['procedure_model_id'] = ["in",$scope.procedures];
                    }
                    return param;
                };
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                Table.api.init({
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'procshutter/view'
                        }
                    ]
                });
                $scope.$broadcast("shownTable");
            }
        },

        bindevent:function($scope) {
            $('[name="row[species_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                    "model": "aptitude",
                };
                return param;
            });
            Form.api.bindevent($("form[role=form]"), $scope.submit);
            if (Config.staff != null)$('[data-field-name="branch"]').hide().trigger("rate");
        },

        api: {

        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});