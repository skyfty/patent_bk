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
            applicant:function($scope, $compile,$timeout, data) {
                var tabscope = angular.element("#tab-applicant").scope();
                $scope.syncCompany = function(){
                    Fast.api.ajax({
                        url: "copyright/syncCompany?ids=" + $scope.row.id
                    }, function (data, ret) {
                        $scope.$apply(function(){
                            $scope.row.found_date = data.found_date;
                            $scope.row.business_licence_code = data.business_licence_code;
                            $scope.row.applicant_name = data.name;
                            if (data.customer != null) {
                                $scope.row.fax = data.customer.fax;
                                $scope.row.telephone = data.customer.telephone;
                                $scope.row.email = data.customer.email;
                                $scope.row.zip_code = data.customer.zip_code;
                                $scope.row.contact = data.customer.name;
                                $scope.row.phone = data.customer.phone_number;
                                $scope.row.mailing_address = data.customer.address + data.customer.detailed_address;
                            }
                        });
                        return false;
                    });
                };
                angular.element("#tab-applicant").html($compile(data)($scope));
                $timeout(function(){
                    angular.element("#data-view-applicant").html($compile(Template("view-tmpl", {}))(tabscope));
                });
            },
            code:function($scope, $compile,$timeout, data) {
                $scope.generate = function(){
                    Fast.api.ajax({
                        url: "dlanguage/generateCode?ids=" + $scope.row.language
                    }, function (data, ret) {
                        $scope.$apply(function(){
                            $scope.row.code = data.code;
                            $scope.row.lines = data.lines;

                        });
                        return false;
                    });
                };
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
                $scope.produceDocument = function() {
                    Layer.confirm(
                        __('确认要重新生成所有文档吗?'), {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function (index) {
                            Fast.api.ajax({
                                url:"/copyright/produce",
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
            },
            crlanguage:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                        "copyright_model_id":$scope.row.id,
                    };
                    return param;
                };
                $scope.fields = data.fields;

                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                Table.api.init({
                    buttons : [
                    ]
                });
                $scope.$broadcast("shownTable");
            },
            crproposer:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                        "copyright_model_id":$scope.row.id,
                    };
                    return param;
                };
                $scope.fields = data.fields;

                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                Table.api.init({
                    extend: {
                        index_url: 'crproposer/index',
                        add_url: 'crproposer/add',
                        del_url: 'crproposer/del',
                        multi_url: 'crproposer/multi',
                        summation_url: 'crproposer/summation',
                        table: 'crproposer',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'crproposer/view'
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