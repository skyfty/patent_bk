define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        //for index
        lands:{
            index:function($scope, $compile,$timeout, data) {

            }
        },
        indexscape:function($scope, $compile,$timeout){
            var species_cascader_id = Fast.api.query("species_cascader_id");
            $scope.speciesModelIds = [];
            $scope.classChanged = function(data) {
                var typeIds = [];
                angular.forEach(data.selected, function(id){
                    if ($.isNumeric(id))
                        typeIds.push(id);
                });
                $scope.speciesModelIds = typeIds;
                $scope.$broadcast("refurbish");
            };


            $scope.searchFieldsParams = function(param) {
                param.custom = {};
                var branchSelect = $('[name="branch_select"]');
                if (branchSelect.data("selectpicker")) {
                    var branchIds = branchSelect.selectpicker('val');
                    if (branchIds && branchIds.length > 0) {
                        param.custom['branch_model_id'] = ["in", branchIds];
                    }
                }
                if (species_cascader_id) {
                    param.custom['species_cascader_id'] = species_cascader_id;
                }
                return param;
            };
            var options = {
                extend: {
                    index_url: 'shuttering/index',
                    add_url: 'shuttering/add',
                    del_url: 'shuttering/del',
                    multi_url: 'shuttering/multi',
                    summation_url: 'shuttering/summation',
                    table: 'shuttering',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-dialog btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'shuttering/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "shuttering/index",dataType: 'json',
                    data:{
                        custom: {"shuttering.id":$scope.row.id}
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
            'procedure_model_id',
            'species_cascader_id'
        ],
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
            var species = null;

            $('[name="row[type]"]').change(function(){
                var type = $(this).val();
                var field = {"name":"file","type":"file"};
                if (typeof Form.formatter[type] == "undefined") {
                    field['type'] = "file";
                } else if (type == "image") {
                    field['type'] = "listing";
                    field['defaultvalue'] = "/model/fields/index";
                }
                if ($scope.row.id) {
                    var html = $(Form.formatter[field['type']]("edit",field, $scope.row['file'], $scope.row));
                } else {
                    var html = $(Form.formatter[field['type']]("add",field, "", {}));
                }
                $('[name="row[file]"]').parents("magicfield").html(html);

                $.ajax({
                    async:false,
                    url:"/species/index",
                    data:{
                        custom:{
                            id:$scope.row.species_cascader_id
                        }
                    }
                }).then(function(data){
                    species = data.rows[0];
                });

                if (type == "image") {
                    $('[name="row[file]"]').data("e-params",function(){
                        var param = {};
                        param.custom = {
                            model_table:species.model,
                            type:"image",
                            alternating:"1",
                        };
                        return param;
                    }).data("e-selected", function(data){
                        $scope.$apply(function(){
                            $scope.row.name = data.row.title;
                        });
                    });
                    $('[name="row[file]"]').val($scope.row['file']);
                    $('[name="row[file]"]').selectPageRefresh();
                }


                Form.api.bindevent($("form[role=form]"), $scope.submit);
            });

            var procedure = null;
            $('[name="row[procedure_model_id]"]').data("e-selected",function(data){
                $('[name="row[catalog_model_id]"]').selectPageDisabled(false);
                procedure = data.row;
            }).data("e-clear",function(data){
                $('[name="row[catalog_model_id]"]').selectPageDisabled(true);
            });
            $('[name="row[catalog_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                    model:(species?species.model:procedure.relevance_model_type)
                };
                return param;
            });
            Form.api.bindevent($("form[role=form]"), $scope.submit);
            require(['selectpage'], function () {
                for (var i in self.initParam) {
                    var param = Backend.api.query(self.initParam[i]);
                    if (param) {
                        $('[name="row[' + self.initParam[i] + ']"]').selectPageDisabled(true);
                    }
                }
                if ($scope.row.id) {
                    $('[name="row[type]"]').trigger("change");
                }
                if ($scope.row.procedure_model_id) {
                    $('[name="row[catalog_model_id]"]').selectPageDisabled(false);
                } else {
                    $('[name="row[catalog_model_id]"]').selectPageDisabled(true);
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