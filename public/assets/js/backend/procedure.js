define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        //for index
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
                        index_url: 'procedure/index',
                        add_url: 'procedure/add',
                        del_url: 'procedure/del',
                        multi_url: 'procedure/multi',
                        summation_url: 'procedure/summation',
                        table: 'procedure',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'procedure/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "procedure/index",dataType: 'json',
                    data:{
                        custom: {"procedure.id":$scope.row.id}
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