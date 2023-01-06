define(['jquery', 'trade', 'table', 'form','template','angular', 'cosmetic'], function ($, Trade, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        index:function() {
            AngularApp.controller("index", function($scope, $compile,$timeout) {
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
                    index_url: 'aptitude/index',
                    add_url: 'aptitude/add',
                    del_url: 'aptitude/del',
                    multi_url: '',
                    summation_url: '',
                    table: 'aptitude',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                        icon: 'fa fa-pencil',
                        url: 'aptitude/edit'
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