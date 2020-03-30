define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
                var options = {
                    extend: {
                        index_url: 'division/index',
                        add_url: 'division/add',
                        del_url: 'division/del',
                        summation_url: 'division/summation',
                        table: 'division',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'division/view'
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
                $.ajax({url: "division/index",dataType: 'json',
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
        initParam:[
            'procedure_model_id'],

        addController:function($scope,$sce, $compile,$timeout) {
            var self = this;
            var defer = $.Deferred();
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
                    self.bindevent($scope, $timeout, defer);
                });
            });
            return defer;
        },

        add: function () {
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                Controller.addController($scope,$sce, $compile,$timeout).then(function(ret){
                    Backend.api.close(ret);
                });
            });
        },
        bindevent:function($scope) {
            var self = this;

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