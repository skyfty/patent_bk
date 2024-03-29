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
                    },
                    {
                        name: 'produce',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs btn-success',
                        icon: 'fa fa-creative-commons',
                        url: 'copyright/produce'
                    },
                    {
                        name: 'download',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs btn-success',
                        icon: 'fa fa-download',
                        url: 'copyright/download'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },

        add: function () {
            AngularApp.controller("edit", function($scope,$sce, $compile,$timeout) {
                $scope.fields = fields;
                $scope.row = row;
                $scope.submit = function(data, ret){
                    Trade.api.close(data);
                    if (ret.code === 1) {
                        parent.Fast.api.open('copyright/code?ids=' + data.id,data.name);
                    }
                    return false;
                };
                $("#data-view").html($compile(Template("edit-tmpl",{state:"add",'fields':"fields"}))($scope));
                $timeout(function(){Form.api.bindevent($("form[role=form]"), $scope.submit);});
            });
        },

        edit: function () {
            Controller.api.assignEditView("edit", row);
        },
        applicant: function () {
            AngularApp.controller("edit", function($scope,$sce, $compile,$timeout) {
                $scope.fields = fields;
                $scope.row = row;
                $scope.syncCompany = function(data, ret){
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
                    return false;
                };
                $("#data-view").html($compile(Template("edit-tmpl",{state:"add",'fields':"fields"}))($scope));
                $timeout(function(){Form.api.bindevent($("form[role=form]"), $scope.submit);});
            });
        },
        code: function () {
            AngularApp.controller("edit", function($scope,$sce, $compile,$timeout) {
                $scope.row = row;
                $scope.submit = function(data, ret){
                    Trade.api.close(data);
                    if (ret.code === 1) {
                        parent.Fast.api.open('/promotion/index?principal_model_id=' + data.company.principal_model_id,"服务项目");
                    }
                    return false;
                };
                $scope.generate = function(){
                    Fast.api.ajax({
                        url: "dlanguage/generateCode?ids=" + $scope.row.dlanguage_model_id
                    }, function (data, ret) {
                        $scope.$apply(function(){
                            $scope.row.code = data.code;
                            $scope.row.lines = data.lines;
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
            $(".nav-tabs-edit [role]").removeClass("active");
            $(".nav-tabs-edit [role='"+Config.actionname+"']").addClass("active");
        },
    };
    Controller.api = $.extend(Controller.api, Trade.api);
    Controller.init();

    return Controller;
});