define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        //for index
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
                        index_url: 'ordinal/index',
                        add_url: 'ordinal/add',
                        del_url: 'ordinal/del',
                        multi_url: 'ordinal/multi',
                        summation_url: 'ordinal/summation',
                        table: 'ordinal',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'ordinal/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "ordinal/index",dataType: 'json',
                    data:{
                        custom: {"ordinal.id":$scope.row.id}
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

        initParam:[
            'policy_model_id','type','principalclass'],
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

        bindevent:function($scope, $timeout, $compile){
            var self = this;
            $scope.initvar = false;
            $('[name="row[syllable_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                    'principalclass': $scope.row['principalclass']
                };
                return param;
            }).data("e-selected", function(data){
                var condition_select = $('[name="row[condition]"]');
                condition_select.empty();

                $('[data-field-name="condition"]').show();
                if ($scope.row.type == "pre" || data.row.type == "selects") {
                    var condition = data.row.condition;
                    var condition_array = condition.split('\n');
                    condition_array.forEach(function(item, index){
                        var option_div = $("<option/>");
                        var option = item.split("|");
                        if (option.length == 2) {
                            option_div.val(option[0]);
                            option_div.html(option[1]);
                        } else {
                            option_div.val(option[0]);
                            option_div.html(option[0]);
                        }
                        condition_select.append(option_div);
                    });

                } else {
                    if (data.row.type =="sql") {
                        $('[data-field-name="condition"]').hide();
                    } else {
                        condition_select.append($compile(Template("spotcircus-condition-tmpl",data.row))($scope));
                    }

                    var rule = [];
                    switch(data.row.type) {
                        case "number": {
                            rule.push("required");
                            rule.push("integer");
                            break;
                        }
                        case "text": {
                            rule.push("required");
                            break;
                        }
                    }
                    $('[role="form"]').validator("setField", 'row[content]', rule.join(";"));
                }
                $('[name="row[condition]"]').off("change");

                if ($scope.row.type == "pre" || data.row.type == "selects") {
                    $('[name="row[condition]"]').on("change", function(){
                        var condition = $(this).val();
                        $('[name="row[content]"]').val(condition);
                    });
                    $('[name="row[content]"]').attr("readonly","readonly");
                    $('[role="form"]').validator("setField", 'row[content]', "");
                } else {
                    $('[name="row[content]"]').removeAttr("readonly");
                }
                condition_select.val($scope.row.condition);

                if ($scope.initvar) {
                    $('[name="row[content]"]').val("");
                    condition_select.trigger("change");
                }
                $scope.initvar = true;
            }).data("e-clear", function(){
                $('[name="row[condition]"]').empty();
            });

            $('[name="row[condition]"]').removeClass("selectpicker");
            Form.api.bindevent($("form[role=form]"));
            require(['selectpage'], function () {
                for (var i in self.initParam) {
                    var param = Backend.api.query(self.initParam[i]);
                    if (param) {
                        $('[name="row[' + self.initParam[i] + ']"]').selectPageDisabled(true);
                    }
                }
            });
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },

        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});