define(['jquery', 'trade', 'table', 'form','template','angular','cosmetic'], function ($, Trade, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        index:function() {
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
                    index_url: 'customer/index',
                    add_url: 'customer/add',
                    del_url: 'customer/del',
                    multi_url: '',
                    summation_url: '',
                    table: 'customer',
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
                    },
                    {
                        name: 'company_add',
                        title: function(row, j){
                            return __('增加公司主体');
                        },
                        text:function(row, j) {
                            return __('公司主体');
                        },
                        classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                        icon: 'fa fa-plus',
                        url:function(row,j) {
                            return 'principal/add/substance_type/company/customer_id/' + row.id;
                        }
                    },
                    {
                        name: 'persion_add',
                        title: function(row, j){
                            return __('增加个人主体');
                        },
                        text:function(row, j) {
                            return __('个人主体');
                        },
                        classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                        icon: 'fa fa-plus',
                        url:function(row,j) {
                            return 'principal/add/substance_type/persion/customer_id/' + row.id;
                        }
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
            Controller.api.assignEditView("edit", row);
        },

        api: {
        },
        init: function () {
        },
    };
    Controller.api = $.extend(Controller.api, Trade.api);
    Controller.init();

    return Controller;
});