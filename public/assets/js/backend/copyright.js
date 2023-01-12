define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {};
                    var branchSelect = $('[name="branch_select"]');
                    if (branchSelect.data("selectpicker")) {
                        var branchIds = branchSelect.selectpicker('val');
                        if (branchIds && branchIds.length > 0) {
                            param.custom['branch_model_id'] = ["in", branchIds];
                        }
                    }
                    return param;
                };
                var options = {
                    extend: {
                        index_url: 'copyright/index',
                        add_url: 'copyright/add',
                        del_url: 'copyright/del',
                        multi_url: 'copyright/multi',
                        summation_url: 'copyright/summation',
                        table: 'copyright',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'copyright/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "copyright/index",dataType: 'json',
                    data:{
                        custom: {"copyright.id":$scope.row.id}
                    },
                    success: function (data) {
                        if (data && data.rows && data.rows.length === 1) {
                            $scope.$apply(function(){
                                $parse("row").assign($scope, data.rows[0]);
                            });
                        }
                    }
                });
            };
        },
        scenery: {
            code:function($scope, $compile,$timeout, data) {
                $scope.generate = function(){
                    Fast.api.ajax({
                        url: "dlanguage/generateCode?ids=" + $scope.row.dlanguage_model_id
                    }, function (data, ret) {
                        $scope.$apply(function(){
                            $scope.row.code = data.code;
                        });
                        return false;
                    });
                }
                angular.element("#tab-" +$scope.scenery.name).html($compile(data)($scope));
                $timeout(function(){
                    var roleForm = $("form[role=form]");
                    var validator = roleForm.data("validator");
                    if (validator) {
                        validator.reset();
                    }
                    Controller.bindevent($scope,$timeout,$compile);
                });
            },
            procshutter:function($scope, $compile,$timeout, data) {
                var dataTable = $("#table-procshutter");

                $scope.produceDocument = function() {
                    Layer.confirm(
                        __('确认要重新生成所有文档吗?'), {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function (index) {
                            Fast.api.ajax({
                                url:"/copyright/produce",
                                data:{
                                    id:$scope.row.id
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
                        "procshutter.relevance_model_type":"copyright",
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
            }
        },
        bindevent:function($scope){
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },

        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});