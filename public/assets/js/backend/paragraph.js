define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                var options = {
                    extend: {
                        index_url: 'paragraph/index',
                        add_url: 'paragraph/add',
                        del_url: 'paragraph/del',
                        summation_url: 'paragraph/summation',
                        table: 'paragraph',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'paragraph/view'
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
                $.ajax({url: "paragraph/index",dataType: 'json',
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

        bindevent:function($scope) {
            Form.api.bindevent($("form[role=form]"), $scope.submit);
            if (Config.staff != null)$('[data-field-name="branch"]').hide().trigger("rate");
        },

        api: {

        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});