define(['jquery', 'trade', 'table', 'form','template','angular','cosmetic'], function ($, Trade, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        index: function () {
            AngularApp.controller("index", function($scope, $compile,$timeout) {
                $scope.sceneryInit = function(idx) {
                    $scope.fields = fields;
                    $timeout(function(){$scope.$broadcast("shownTable");});
                };


                $scope.searchFieldsParams = function(param) {
                    param.custom = {};
                    var principal_model_id = Fast.api.query("principal_model_id");
                    if (principal_model_id) {
                        param.custom['principal_model_id'] = principal_model_id;
                    }
                    return param;
                };

            });

            var options = {
                showToggle: false,
                showColumns: false,
                showExport: false,
                commonSearch: false, //是否启用通用搜索
                extend: {
                    index_url: 'promotion/index',
                    add_url: 'promotion/add',
                    del_url: 'promotion/del',
                    multi_url: '',
                    summation_url: '',
                    table: 'promotion',
                },
                buttons : [
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },

        init: function () {
        },
        api: {
        }
    };
    Controller.api = $.extend(Controller.api, Trade.api);
    Controller.init();
    return Controller;
});