define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','bootstrap-treegrid','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,undefined,undefined) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        indexscape:function($scope, $compile,$timeout){
            var dataTable = $("[ui-formidable]");
            $scope.selectnode = null;

            $scope.onTreeClick = function(event, treeId, node, clickFlag) {
                $scope.selectnode = node;
                var status = $scope.selectnode.status;
                $(".btn-edit-lecatenate").toggleClass('disabled', status == "locked");
                $(".btn-del-lecatenate").toggleClass('disabled', status == "locked");
                dataTable.bootstrapTable('refresh');
            };

            var setting = {
                async: {
                    enable: true,
                    url:"/lecture/classtree",
                    autoParam:[
                        "id", "name=n", "level=lv"
                    ],

                },
                data: {
                    simpleData: {
                        enable: true
                    }
                },
                view:{
                },
                callback: {
                    onAsyncSuccess: function(event, treeId, treeNode, msg) {
                        if (treeNode == null) {
                            $timeout(function(){
                                var zTree = $.fn.zTree.getZTreeObj("channeltree");
                                var nodes = zTree.getNodes();
                                for (var i=0, l=nodes.length; i<l; i++) {
                                    zTree.expandNode(nodes[i], true, false, false);
                                }
                                zTree.selectNode( zTree.getNodeByParam("id", 0, null));
                            });
                        }
                        return true;
                    },
                    onClick: $scope.onTreeClick,
                }
            };
            $.fn.zTree.init($("#channeltree"), setting);

            $scope.addCatenate = function(){
                var url = "/lecture/add?type=catenate";
                if ($scope.selectnode) {
                    url += "&pid=" + $scope.selectnode.id;
                }
                Backend.api.open(url, "新流程", {
                    callback: function (res) {
                        res.isParent = true;
                        res.open = true;
                        $.fn.zTree.getZTreeObj("channeltree").addNodes($scope.selectnode,-1, res);
                    }}
                );
                return false;
            };

            $scope.editCatenate = function(){
                if ($scope.selectnode) {
                    var url = "/lecture/edit?";
                    if ($scope.selectnode) {
                        url += "ids=" + $scope.selectnode.id;
                    }
                    Backend.api.open(url, "修改流程", {
                        callback: function (res) {
                            $scope.selectnode.name = res.name;
                            $.fn.zTree.getZTreeObj("channeltree").updateNode($scope.selectnode);
                        }}
                    );
                }
                return false;
            };

            $scope.delCatenate = function(){
                if ($scope.selectnode) {
                    Layer.confirm( "确定要删除这个流程吗？",
                        {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function (index) {
                            Backend.api.ajax({url:"/lecture/del/ids/" + $scope.selectnode.id}, function(){
                                $.fn.zTree.getZTreeObj("channeltree").removeNode($scope.selectnode);
                            });
                            Layer.close(index);
                        }
                    );
                }
                return false;
            };

            var options = {
                extend: {
                    index_url: 'lecture/index',
                    add_url: 'lecture/add',
                    del_url: 'lecture/del',
                    summation_url: 'lecture/summation',
                    table: 'lecture',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __('%s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'lecture/view'
                    }
                ]
            };

            $scope.searchFieldsParams = function(param) {
                param.custom = {'lecture.type':'lecture'};
                if ($scope.selectnode) {
                    param.custom['lecture.pid'] = $scope.selectnode.id;
                }
                return param;
            };

            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));

            $(".btn-add-lecture").click(function(){
                var url = "/lecture/add?type=lecture";
                if ($scope.selectnode) {
                    url += "&pid=" + $scope.selectnode.id;
                }
                Backend.api.open(url, "添加魔板");
                return false;
            });
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "lecture/index",dataType: 'json',
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
            courseware:function($scope, $compile,$timeout, data) {
                
                $scope.detailFormatter = function (index, row) {
                    var html = [];
                    html.push('<p><b>整句:</b> ' +  row.detail.join('') + '</p>');

                    return html.join('');
                };

                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "lecture_model_id": $scope.row.id,
                    };
                    return param;
                };
                Table.api.init({
                    extend: {
                        index_url: 'courseware/index',
                        edit_url: 'courseware/edit',
                        del_url: 'courseware/del',
                        table: 'courseware',
                    },
                    commonSearch: false, //是否启用通用搜索
                    showExport: false,
                    search: false, //是否启用快速搜索
                });
                $scope.fields = data.fields;

                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");

                $(".btn-add-courseware").click(function(){
                    var url = '/courseware/edit?lecture_model_id=' + $scope.row.id;
                    var index = Backend.api.open(url, "新建流程", {
                        callback: function (res) {
                        }}
                    );
                    Layer.full(index);
                    return false;
                });
            },
        },

        add: function () {
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.row = {};
                $scope.row['owners_model_id'] = Config.admin_id;
                $scope.row['branch_model_id'] = Config.staff?Config.staff.branch_model_id:0;

                $scope.submit = function(data, ret){
                    if ($(document.body).hasClass("is-dialog")) {
                        Backend.api.close(data);
                    } else {
                        Backend.api.addtabs("lecture/view/ids/" + data.id, __('%s',data.idcode));
                        Backend.api.closetabs('lecture/add');
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

        edit:function() {
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                Backend.api.close(data);
            });
        },

        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});