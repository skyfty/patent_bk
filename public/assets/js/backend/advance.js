define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                $scope.formatter = function(field, data, row) {

                };

                var options = {
                    extend: {
                        index_url: 'alternating/index',
                        add_url: 'alternating/add',
                        del_url: 'alternating/del',
                        summation_url: 'alternating/summation',
                        table: 'alternating',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'alternating/view'
                        }
                    ]
                };
                Table.api.init(options);
                var table = $("#table-index");

                $scope.searchFieldsParams = function(param) {
                    param.custom = {};

                    return param;
                };

                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "alternating/index",dataType: 'json',
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
            $scope.fieldFormatter =Controller.api.fieldFormatter;

        },
        scenery: {

        },

        initParam:[
            'procedure_model_id','type'],
        add: function () {
            var self = this;
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout){
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
                        self.bindevent($scope, $timeout,$compile);
                    });
                });
            });
        },

        bindevent:function($scope){
            var self = this;

            var procedure = null;
            $('[name="row[procedure_model_id]"]').data("e-params",function(){
                var param = {};
                return param;
            }).data("e-selected", function(data){
                procedure = data.row;
                $('[name="row[field_model_id]"]').selectPageClear();
            });

            $('[name="row[type]"]').change(function(){
                var type = $(this).val();
                var field = {"name":"field","type":"model"};
                if (type == "custom") {
                    field['name'] = "field_model_id";
                    field['type'] = "select";
                    field['content_list'] = Controller.api.convertFieldName();
                    field['defaultvalue'] = "date";
                    field['remark'] = "sdfsdfdsf";
                } else {
                    field['defaultvalue'] = "/model/fields/index";
                }
                if ($scope.row.id) {
                    var html = $(Form.formatter[field['type']]("edit",field, $scope.row['field_model_id'], $scope.row));
                } else {
                    var html = $(Form.formatter[field['type']]("add",field, "", {}));
                }
                $('[name="row[field_model_id]"]').parents("magicfield").html(html);

                if (type == "default") {
                    $('[name="row[field_model_id]"]').data("e-params",function(){
                        var param = {};
                        param.custom = {
                            "model_table":procedure.relevance_model_type,
                            alternating:"1",
                        };
                        return param;
                    }).data("e-selected", function(data){
                        $scope.$apply(function(){
                            $scope.row.name = data.row.title;
                        });
                    });
                    $('[name="row[field_model_id]"]').val($scope.row['field_model_id']);
                    $('[name="row[field_model_id]"]').selectPageRefresh();
                }
                setTimeout(function(){
                    if (type == "default") {
                        $('[name="row[scope]"]').val($scope.row['scope'] = "global")
                        $('[data-field-name="scope"]').hide().trigger("rate");
                    } else {
                        $('[data-field-name="scope"]').show().trigger("rate");
                    }
                },0);

                Form.api.bindevent($("form[role=form]"), $scope.submit);
            });

            Form.api.bindevent($("form[role=form]"), $scope.submit);
            require(['selectpage'], function () {
                for (var i in self.initParam) {
                    if (self.initParam[i] == "type") {
                        //$('[name="row[type]"]').attr("disabled","disabled").val($scope.row['type']);
                    } else {
                        var param = Backend.api.query(self.initParam[i]);
                        if (param) {
                            $('[name="row[' + self.initParam[i] + ']"]').selectPageDisabled(true);
                        }
                    }
                }
                $('[name="row[type]"]').trigger("change");

            });
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },


        api: {
            fieldFormatter:function(field, data, row) {
                if (field.name == "field" && data['type'] == "custom") {
                    return Controller.api.convertFieldName(data['field_model_id'])
                } else {
                    return Cosmetic.api.formatter(field, data, row);
                }
            }
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});