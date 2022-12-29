define(['jquery', 'trade', 'table', 'form','template','angular','cosmetic'], function ($, Trade, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        index: function () {
            AngularApp.controller("index", function($scope, $compile,$timeout) {
                $scope.detailFormater = function (index, row) {
                    return Template("customer-detail-tmpl",row);
                };

                $scope.sceneryInit = function(idx) {
                    $scope.fields = fields;
                    $timeout(function(){$scope.$broadcast("shownTable");});
                };
            });

            var options = {
                showToggle: false,
                showColumns: false,
                showExport: false,
                commonSearch: false, //是否启用通用搜索
                extend: {
                    index_url: 'principal/index',
                    add_url: 'principal/add',
                    del_url: 'principal/del',
                    multi_url: '',
                    summation_url: '',
                    table: 'principal',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                        icon: 'fa fa-pencil',
                        url: 'customer/edit'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },

        add: function () {
            Controller.api.assignEditView("add", {});

        },

        edit: function () {
            Controller.api.assignEditView("edit", row[row['substance_type']]);

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