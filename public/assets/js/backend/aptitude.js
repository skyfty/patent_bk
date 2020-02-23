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