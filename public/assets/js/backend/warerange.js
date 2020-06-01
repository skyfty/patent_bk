define(['jquery', 'bootstrap', 'backend', 'table', 'form','bootstrap-treegrid','angular','cosmetic','ztree'], function ($, undefined, Backend, Table, Form, undefined,angular, Cosmetic, undefined) {

    var Controller = {
        index: function () {

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        select: function () {
            AngularApp.controller("select", function($scope,$sce, $compile,$timeout) {
                var table = $("#table");
                var channeltree = $("#channeltree");
                var pid = channeltree.data("pid");
                var promotionModelId = channeltree.data("promotionid");
                var lids = channeltree.data("lids");

                $scope.selectnode = null;
                $scope.onTreeClick = function(event, treeId, node, clickFlag) {
                    $scope.selectnode = node;
                    table.bootstrapTable('refresh');
                };
                $scope.onTreeCreated = function(event, treeId, treeNode) {
                    if ($scope.selectnode == null) {
                        var zTree = $.fn.zTree.getZTreeObj(treeId);
                        zTree.selectNode(treeNode);$scope.onTreeClick(event, treeId, treeNode);
                    }
                };
                function filter(treeId, parentNode, childNodes) {
                    if (!childNodes) return null;
                    for (var i=0, l=childNodes.length; i<l; i++) {
                        childNodes[i].name = childNodes[i].name.replace(/\.n/g, '.');
                    }
                    return childNodes;
                }
                var curStatus = "init", curAsyncCount = 0, asyncForAll = false,goAsync = false;

                $scope.beforeAsync = function () {
                    curAsyncCount++;
                };

                $scope.onAsyncSuccess = function (event, treeId, treeNode, msg) {
                    curAsyncCount--;
                    if (curStatus == "expand") {
                        $scope.expandNodes(treeNode.children);
                    } else if (curStatus == "async") {
                        $scope.asyncNodes(treeNode.children);
                    }

                    if (curAsyncCount <= 0) {
                        if (curStatus != "init" && curStatus != "") {
                            asyncForAll = true;
                        }
                        curStatus = "";
                    }
                };

                $scope.onAsyncError =function (event, treeId, treeNode, XMLHttpRequest, textStatus, errorThrown) {
                    curAsyncCount--;

                    if (curAsyncCount <= 0) {
                        curStatus = "";
                        if (treeNode!=null) asyncForAll = true;
                    }
                };

                var setting = {
                    data: {
                        simpleData: {
                            enable: true
                        }
                    },
                    async: {
                        enable: true,
                        url:"/warerange/classtree",
                        autoParam:[
                            "id", "name=n", "level=lv"
                        ],
                        otherParam:{
                            "pid":pid,
                            "promotion":promotionModelId,
                            "lids":lids,
                        },
                    },
                    callback: {
                        onClick:$scope.onTreeClick,
                        onNodeCreated: $scope.onTreeCreated,
                        beforeAsync: $scope.beforeAsync,
                        onAsyncSuccess: $scope.onAsyncSuccess,
                        onAsyncError: $scope.onAsyncError
                    }
                };
                $.fn.zTree.init($("#channeltree"), setting);

                $scope.asyncNodes = function(nodes) {
                    if (!nodes) return;
                    curStatus = "async";
                    var zTree = $.fn.zTree.getZTreeObj("channeltree");
                    for (var i=0, l=nodes.length; i<l; i++) {
                        if (nodes[i].isParent && nodes[i].zAsync) {
                            $scope.asyncNodes(nodes[i].children);
                        } else {
                            goAsync = true;
                            zTree.reAsyncChildNodes(nodes[i], "refresh", true);
                        }
                    }
                };

                $scope.expandAll = function () {
                    var zTree = $.fn.zTree.getZTreeObj("channeltree");
                    if (asyncForAll) {
                        zTree.expandAll(true);
                    } else {
                        $scope.expandNodes(zTree.getNodes());
                        if (!goAsync) {
                            curStatus = "";
                        }
                    }
                };
                $scope.expandNodes = function (nodes) {
                    if (!nodes) return;
                    curStatus = "expand";
                    var zTree = $.fn.zTree.getZTreeObj("channeltree");
                    for (var i=0, l=nodes.length; i<l; i++) {
                        zTree.expandNode(nodes[i], true, false, false);
                        if (nodes[i].isParent && nodes[i].zAsync) {
                            $scope.expandNodes(nodes[i].children);
                        } else {
                            goAsync = true;
                        }
                    }
                };
                $timeout(function(){$scope.expandAll();}, 300);

                $("#btn-select").click(function(){
                    var ids = Table.api.selectedids(table);
                    Backend.api.close(ids);
                });

                var options = {
                    extend: {
                        index_url: 'warehouse/index',
                        table: 'warehouse',
                    }
                };
                Table.api.init(options);

                var tableOptions = {
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    escape: false,
                    pk: 'id',
                    showExport: false,
                    showColumns: false,
                    cardView: false, //卡片视图
                    commonSearch: false, //是否启用通用搜索
                    checkOnInit:false,
                    singleSelect: true, //是否启用单选
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'name', title: __('name')},
                        ]
                    ],
                    queryParams: function (params) {
                        params.custom = {};
                        if ($scope.selectnode) {
                            params.custom['pid'] = $scope.selectnode.id;
                        } else if (pid) {
                            params.custom['pid'] = pid;
                        }

                        if ($scope.selectnode && $scope.selectnode.model_type) {
                            params.model_type = $scope.selectnode.model_type;
                        }

                        if ($scope.selectnode && promotionModelId) {
                            params.promotion_model_id = promotionModelId;
                            params.relation_type = $scope.selectnode.relation_type;
                        }
                        return params;
                    }
                };
                table.bootstrapTable(tableOptions);
            });

        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"), function(ret){
                    Backend.api.close(ret);
                });
            }
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});