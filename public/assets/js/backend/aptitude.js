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
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'aptitude/view'
                        }
                    ]
                };
                Table.api.init(options);
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
            procshutter:function($scope, $compile,$timeout, data) {
                $scope.produceDocument = function() {
                    Layer.confirm(
                        __('确认要重新生成所有文档吗?'), {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function (index) {
                            Fast.api.ajax({
                                url:"/aptitude/produce",
                                data:{
                                    ids:$scope.row.id
                                }
                            }, function(){
                                $scope.$broadcast("refurbish");
                            });
                            Layer.close(index);
                        }
                    );
                };

                $scope.formaterColumn = function(j, data) {
                    if (data.field === "file") {
                        data.formatter = function (value, row, index) {
                            var html = Table.api.formatter.files.call(this, value, row, index);
                            var exticon =  Table.api.formatter.mapfileicon.call(this, value);
                            if (exticon === "fa-file-word-o") {
                                html += " <a target='_blank'  download='"+row.name+".pdf' href='/procshutter/topdf?id="+row.id+"' alt='下载PDF格式'><i  class='fa fa-file-pdf-o'></i></a>";
                            }
                            return html;
                        }
                    }
                    return data;
                };

                $scope.procedures = [];

                $scope.classChanged = function(data) {
                    var procedures = [];
                    angular.forEach(data.selected, function(id){
                        if ($.isNumeric(id))
                            procedures.push(id);
                    });
                    $scope.procedures = procedures;
                    $scope.$broadcast("refurbish");
                };
                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                        "procshutter.relevance_model_type":"aptitude",
                        "procshutter.relevance_model_id":$scope.row.id,
                    };

                    if ($scope.procedures.length > 0) {
                        param.custom['procshutter.procedure_model_id'] = ["in",$scope.procedures];
                    }
                    return param;
                };
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                Table.api.init({
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'procshutter/view'
                        }
                    ]
                });
                $scope.$broadcast("shownTable");
            },
            progress:function($scope, $compile,$timeout, data) {
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
            }
        },

        add: function () {
            var self = this;
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.row = {};
                $scope.row['owners_model_id'] = Config.admin_id;
                $scope.row['branch_model_id'] = Config.staff?Config.staff.branch_model_id:0;

                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope));
                    $timeout(function(){
                        $('[name="row[company_model_id]"]').data("e-selected",function(data){
                            if (data && data.row) {
                                if (data.row.business_licence) {
                                    $('[name="row[business_licence]"]').val(data.row.business_licence).trigger("change");
                                }
                            }
                        });

                        self.bindevent($scope);
                    });
                });
            });
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