define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        //for index
        lands:{
            index:function($scope, $compile,$timeout, data) {
                var dataTable = $("#table-index");
                var stem = null;
                $('#stem_index').data("e-params",function(){
                    var param = {};
                    return param;
                }).data("e-selected", function(data){
                    stem = data.row;
                    dataTable.bootstrapTable('refresh', {});
                }).data("e-clear", function(){
                    stem = null;
                    dataTable.bootstrapTable('refresh', {});
                });
                $scope.searchFieldsParams = function(param) {
                    param.custom = {};
                    if (stem != null) {
                        param.custom['stem_model_id'] = stem.id;
                    }
                    return param;
                };

                var options = {
                    extend: {
                        index_url: 'wealth/index',
                        add_url: '',
                        del_url: 'wealth/del',
                        multi_url: 'wealth/multi',
                        summation_url: 'wealth/summation',
                        table: 'wealth',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'wealth/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "wealth/index",dataType: 'json',
                    data:{
                        custom: {"wealth.id":$scope.row.id}
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