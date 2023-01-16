define(['jquery', 'trade', 'table', 'form','template','angular', 'cosmetic'], function ($, Trade, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        index:function() {
            AngularApp.controller("index", function($scope, $compile,$timeout) {
                $scope.sceneryInit = function(idx) {
                    $scope.fields = fields;
                    $timeout(function(){$scope.$broadcast("shownTable");});
                };

                $scope.searchFieldsParams = function(param) {
                    param.custom = {};
                    var ids = Fast.api.query("ids");
                    if (ids) {
                        param.custom['id'] = ids;
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
                    index_url: 'copyright/index',
                    add_url: 'copyright/add',
                    del_url: 'copyright/del',
                    multi_url: '',
                    summation_url: '',
                    table: 'copyright',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                        icon: 'fa fa-pencil',
                        url: 'copyright/edit'
                    },
                    {
                        name: 'code',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs btn-success btn-magic btn-dialog',
                        icon: 'fa fa-code',
                        url: 'copyright/code'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },

        add: function () {
            Controller.api.assignEditView("add", row);
        },

        edit: function () {
            Controller.api.assignEditView("edit", row);
        },

        code: function () {
            AngularApp.controller("edit", function($scope,$sce, $compile,$timeout) {
                $scope.row = row;
                $scope.submit = function(data, ret){
                    Trade.api.close(data);
                };
                $scope.generate = function(){
                    Fast.api.ajax({
                        url: "dlanguage/generateCode?ids=" + $scope.row.dlanguage_model_id
                    }, function (data, ret) {
                        $scope.$apply(function(){
                            $scope.row.code = data.code;
                        });
                        return false;
                    });
                };
                $timeout(function(){Form.api.bindevent($("form[role=form]"), $scope.submit);});
            });
        },
        api: {
            formatFields:function(fields, row) {
                for(var j = 0; j < fields.length; ++j) {
                    fields[j].data = Cosmetic.api.formatRow(fields[j], row);
                }
                return fields;
            },
        },
        init: function () {
        },
    };
    Controller.api = $.extend(Controller.api, Trade.api);
    Controller.init();

    return Controller;
});