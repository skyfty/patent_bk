define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','bootstrap-treegrid'], function ($, Backend, Table, Form, Template,angular, Cosmetic,undefined) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        indexscape:function($scope, $compile,$timeout){
            var dataTable = $("[ui-formidable]");
            $scope.selectnode = null;

            $scope.clickChannel = function(event, treeId, node, clickFlag) {
                $scope.selectnode = node;
                $(".btn-edit-warerange").toggleClass('disabled', $scope.selectnode.status == "locked");
                $(".btn-del-warerange").toggleClass('disabled', $scope.selectnode.status == "locked");
                $(".btn-add-warehouse").toggleClass('disabled', $scope.selectnode.surface == 1);
                $(".btn-del").toggleClass('disabled', $scope.selectnode.surface == 1);
                dataTable.bootstrapTable('refresh');
            };

            $scope.addWarerange = function(){
                var url = "/warerange/add?";
                if ($scope.selectnode) {
                    url += "pid=" + $scope.selectnode.id;
                }
                Backend.api.open(url, "新分类", {
                    callback: function (res) {
                        res.isParent = true;
                        res.open = true;
                        $.fn.zTree.getZTreeObj("channeltree").addNodes($scope.selectnode,-1, res);
                    }}
                );
                return false;
            };

            $scope.editWarerange = function(){
                if ($scope.selectnode) {
                    var url = "/warerange/edit?";
                    if ($scope.selectnode) {
                        url += "ids=" + $scope.selectnode.id;
                    }
                    Backend.api.open(url, "修改", {
                        callback: function (res) {
                            $scope.selectnode.name = res.name;
                            $.fn.zTree.getZTreeObj("channeltree").updateNode($scope.selectnode);

                        }}
                    );
                }
                return false;
            };

            $scope.delWarerange = function(){
                if ($scope.selectnode) {
                    Layer.confirm( "确定要删除这个分类吗？",
                        {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function (index) {
                           Backend.api.ajax("/warerange/del/ids/" + $scope.selectnode.id);
                        }
                    );
                }
                return false;
            };

            var options = {
                extend: {
                    index_url: 'warehouse/index',
                    add_url: 'warehouse/add',
                    del_url: 'warehouse/del',
                    summation_url: 'warehouse/summation',
                    table: 'warehouse',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __('%s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'warehouse/view'
                    }
                ]
            };

            $scope.searchFieldsParams = function(param) {
                param.custom = {};
                if ($scope.selectnode) {
                    param.custom['pid'] = $scope.selectnode.id;
                }
                return param;
            };

            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));

            $(".btn-add-warehouse").click(function(){
                var url = "/warehouse/add?";
                if ($scope.selectnode) {
                    url += "pid=" + $scope.selectnode.id;
                }
                Backend.api.open(url, "添加魔板");
                return false;
            });
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "warehouse/index",dataType: 'json',
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
            assembly:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "warehouse_model_id": $scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    extend: {
                        del_url: 'assembly/del',
                        summation_url: 'assembly/summation',
                        table: 'assembly',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic  btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'assembly/view'
                        }
                    ]

                });
                $scope.fields = data.fields;
                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            }
        },

        add: function () {
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.row = {};
                $scope.row['owners_model_id'] = Config.admin_id;
                $scope.row['branch_model_id'] = Config.staff?Config.staff.branch_model_id:0;

                $scope.submit = function(data, ret){
                    if ($(document.body).hasClass("is-dialog")) {
                        Backend.api.close();
                    } else {
                        Backend.api.addtabs("warehouse/view/ids/" + data.id, __('%s',data.idcode));
                        Backend.api.closetabs('warehouse/add');
                    }
                };
                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope));
                    $timeout(function(){
                        Form.api.bindevent($("form[role=form]"), $scope.submit);
                    });
                });
            });
        },
        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});