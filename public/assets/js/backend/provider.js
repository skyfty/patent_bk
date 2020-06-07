define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic', 'moment','bootstrap-datetimepicker'], function ($, Backend, Table, Form, Template,angular, Cosmetic, moment, datepicker) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                var dataTable = $("[ui-formidable]");

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

                $scope.$on('shownTab', function(event,data) {
                    $scope.$broadcast("shownTable");
                });

                $scope.searchFieldsParams = function(param) {
                    param.custom = {};
                    if ($scope.genreModelIds.length > 0) {
                        param.custom['genre_cascader_id'] = ["in",$scope.genreModelIds];
                    }
                    var branchSelect = $('[name="branch_select"]');
                    if (branchSelect.data("selectpicker")) {
                        var branchIds = branchSelect.selectpicker('val');
                        if (branchIds && branchIds.length > 0) {
                            param.custom['branch_model_id'] = ["in", branchIds];
                        }
                    }
                    return param;
                };

                $scope.tableExtendButtons = function(param) {
                    return param;
                };


                $scope.accomplish = function() {
                    var that = this;
                    var ids = Table.api.selectedids(dataTable);
                    Layer.confirm(
                        __('确认要完成 %s 个课程订单吗?', ids.length), {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function (index) {
                            Table.api.multi("accomplish", ids, dataTable, that);
                            Layer.close(index);
                        }
                    );
                };

                var options = {
                    extend: {
                        index_url: 'provider/index',
                        add_url: 'provider/add',
                        del_url: 'provider/del',
                        multi_url: 'provider/multi',
                        summation_url: 'provider/summation',
                        accomplish_url:'provider/accomplish',
                        table: 'provider',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'provider/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                var defer = $.Deferred();
                $.ajax({url: "provider/index",dataType: 'json',
                    data:{
                        custom:{
                            "provider.id":$scope.row.id
                        }
                    },
                    success: function (data) {
                        if (data && data.rows && data.rows.length == 1) {
                            $scope.$apply(function(){
                                $parse("row").assign($scope, data.rows[0]);
                            });
                            defer.resolve(data.rows);
                        }
                    }
                });
                return defer;
            };
            $(".btn-ajax").data("success", $scope.refreshRow);
        },
        scenery: {
        },
        initParam:[],

        addController:function($scope,$sce, $compile,$timeout) {

        },

        add: function () {
            var self = this;
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.pre ={}; $scope.row = {};
                $scope.row['branch_model_id'] = Config.admin_branch_model_id!= null?Config.admin_branch_model_id:0;
                $scope.row['creator_model_id'] = $scope.row['owners_model_id'] = Config.admin_id;

                for(var i in self.initParam) {
                    var param = Backend.api.query(self.initParam[i]);
                    if (param) {
                        $scope.pre[self.initParam[i]] = $scope.row[self.initParam[i]] = param;
                    }
                }
                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope));
                    $timeout(function(){
                        self.bindevent($scope, $timeout);
                    });
                });
            });
        },

        bindevent:function($scope,$timeout){
            var self = this;
            $('[name="row[promotion_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                    "species_cascader_id": $scope.row['species_cascader_id']
                };
                return param;
            });
            Form.api.bindevent($("form[role=form]"));

            require(['selectpage'], function () {

            });
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },

        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.refresh = function(){
                    $.ajax({url: "provider/statistic",dataType: 'json',cache: false,
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