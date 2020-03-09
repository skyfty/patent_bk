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
            'policy_model_id'],
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
                        self.bindevent($scope, $timeout);
                    });
                });
            });
        },

        bindevent:function($scope, $timeout){
            var self = this;
            $('[name="row[syllable_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                };
                return param;
            }).data("e-selected", function(data){
                var condition_select = $('[name="row[condition]"]');
                condition_select.empty();

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
                condition_select.selectpicker('refresh').selectpicker('render');
                condition_select.trigger("change");
            });

            Form.api.bindevent($("form[role=form]"));
            require(['selectpage'], function () {
                for (var i in self.initParam) {
                    var param = Backend.api.query(self.initParam[i]);
                    if (param) {
                        $('[name="row[' + self.initParam[i] + ']"]').selectPageDisabled(true);
                    }
                }

                $('[name="row[condition]"]').on("change", function(){
                    $('[name="row[content]"]').val($(this).val());
                });
            });

            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },

        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});