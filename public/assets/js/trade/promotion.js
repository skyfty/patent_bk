define(['jquery', 'trade', 'table', 'form','template','angular','cosmetic'], function ($, Trade, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        index: function () {
            AngularApp.controller("index", function($scope, $compile,$timeout) {
                $scope.sceneryInit = function(idx) {
                    $scope.fields = fields;
                    $timeout(function(){$scope.$broadcast("shownTable");});
                };

                $scope.detailFormater = function (index, row) {
                    var html = Template("detail-tmpl", {row:row});
                    require(['masonry'], function(Masonry){
                        // init with selector
                        new Masonry( '.grid',{
                            // options
                            itemSelector: '.grid-item',
                            columnWidth: 360
                        });
                    });
                    return html;
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
                    edit_url: 'promotion/edit',
                    multi_url: '',
                    summation_url: '',
                    table: 'promotion',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs btn-success',
                        icon: 'fa fa-list',
                        url: function(row, j){
                            return "/" + row.relevance_model_type + "/index/principal_model_id/" + row.relevance_model_id;
                        },
                        extend:'target="_blank" '
                    },
                    {
                        name: 'edit',
                        title: function(row, j){
                            return "编辑";
                        },
                        classname: 'btn btn-xs btn-success  btn-dialog',
                        icon: 'fa fa-pencil',
                        url: function(row, j){
                            return "/" + row.relevance_model_type + "/edit/ids/" + row.relevance_model_id;
                        },
                    },
                    {
                        name: 'produce',
                        title: function(row, j){
                            return "生成文档";
                        },
                        classname: 'btn btn-xs btn-success btn-ajax',
                        icon: 'fa fa-creative-commons',
                        confirm:function(row, j) {
                            return "重新生成文档？";
                        },
                        url: function(row, j){
                            return "/" + row.relevance_model_type + "/produce/ids/" + row.relevance_model_id;
                        },
                    },
                    {
                        name: 'download',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs btn-success',
                        icon: 'fa fa-download',
                        url: function(row, j){
                            return "/" + row.relevance_model_type + "/download/ids/" + row.relevance_model_id;
                        },
                    }
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