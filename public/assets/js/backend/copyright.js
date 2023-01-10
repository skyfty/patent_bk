define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
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
                        index_url: 'copyright/index',
                        add_url: 'copyright/add',
                        del_url: 'copyright/del',
                        multi_url: 'copyright/multi',
                        summation_url: 'copyright/summation',
                        table: 'copyright',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'copyright/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "copyright/index",dataType: 'json',
                    data:{
                        custom: {"copyright.id":$scope.row.id}
                    },
                    success: function (data) {
                        if (data && data.rows && data.rows.length === 1) {
                            $scope.$apply(function(){
                                $parse("row").assign($scope, data.rows[0]);
                            });
                        }
                    }
                });
            };
        },
        scenery: {
            code:function($scope, $compile,$timeout, data) {
                $scope.generate = function(){
                    Fast.api.ajax({
                        url: "ajax/generateCode?ids=" + $scope.row.dlanguage_model_id
                    }, function () {

                        return false;
                    });
                }
                angular.element("#tab-" +$scope.scenery.name).html($compile(data)($scope));
            },
            procshutter:function($scope, $compile,$timeout, data) {

            }
        },
        bindevent:function($scope){
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },

        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});