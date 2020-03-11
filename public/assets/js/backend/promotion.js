define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                var options = {
                    extend: {
                        index_url: 'promotion/index',
                        add_url: '',
                        del_url: 'promotion/del',
                        summation_url: 'promotion/summation',
                        table: 'promotion',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'promotion/view',
                            extend: 'data-toggle="tooltip"',
                        }
                    ]
                };
                Table.api.init(options);
                var table = $("#table-index");

                $scope.genreModelIds = [];
                $scope.classChanged = function(data) {
                    var typeIds = [];
                    angular.forEach(data.selected, function(id){
                        if ($.isNumeric(id))
                            typeIds.push(id);
                    });
                    $scope.genreModelIds = typeIds;
                    $scope.$broadcast("refurbish");
                };

                $scope.formatterOperate = function(value, row, index) {
                    var buttons = Table.api.formatter.operate.call(this,value, row, index);
                    return buttons;
                };

                $scope.searchFieldsParams = function(param) {
                    param.custom = {};
                    if ($scope.genreModelIds.length > 0) {
                        param.custom['genre_cascader_id'] = ["in",$scope.genreModelIds];
                    }
                    return param;
                };

                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },

        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "promotion/index",dataType: 'json',
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
            var self = this;

            $('[name="row[relevance_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                    "branch_model_id":$scope.row['branch_model_id']
                };
                return param;
            });

            $('[name="row[species_cascader_id]"]').change(function(){
                var relevance_model = $('[name="row[relevance_model_id]"]');
                relevance_model.selectPageClear();
                if ($scope.row.species_cascader_keyword) {
                    var species = JSON.parse($scope.row.species_cascader_keyword);
                    var url = species.row.model + "/index";
                    relevance_model.selectPageDataUrl(url);
                }
            });

            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                $scope.submit(data, ret);
            });

            require(['selectpage'], function () {
                $('[name="row[species_cascader_id]"]').trigger("change");
            });
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },

        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "promotion/statistic",dataType: 'json',cache: false,
                        success: function (ret) {
                            $scope.$apply(function(){
                                $scope.stat = ret.data;
                            });
                        }
                    });
                };
                $scope.$on("refurbish", $scope.refresh);$scope.refresh(); $(".btn-refresh").on("click", $scope.refresh);
            });
        },
        api: {

        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});