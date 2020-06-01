define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','bootstrap-treegrid','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,undefined,Ztree) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
        },
        scenery: {
            preset:function($scope, $compile,$timeout, data) {
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
                        index_url: 'preset/index',
                        edit_url: 'preset/edit',
                        del_url: 'preset/del',
                        table: 'preset',
                    },
                    commonSearch: false, //是否启用通用搜索
                    showExport: false,
                    search: false, //是否启用快速搜索
                });
                $scope.fields = data.fields;

                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");

                $(".btn-add-preset").click(function(){
                    var url = '/preset/edit?prelecture_model_id=' + $scope.row.id;
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
                $scope.selectnode = null;
                var setting = {
                    data: {
                        simpleData: {
                            enable: true
                        }
                    },
                    check: {
                        enable: true
                    },
                    callback: {
                        onClick: function(event, treeId, treeNode, clickFlag) {
                            $scope.selectnode = treeNode;
                            return true;
                        }
                    }
                };
                var channeltree = $("#channeltree");
                $.ajax({
                    url:"lecture/alltree",
                    data:{
                        pid:channeltree.data("pid")
                    }
                }).then(function(ret){
                    $.fn.zTree.init(channeltree, setting, ret);
                });

                Form.api.bindevent($("form[role=form]"), function(ret){
                    Backend.api.close(ret);
                }, null, function(){
                    var lectures = [];
                    var nodes = $.fn.zTree.getZTreeObj("channeltree").getCheckedNodes(true);
                    angular.forEach(nodes, function(node){
                        if (node.type == "lecture") {
                            lectures.push(node);
                        }
                    });

                    var lecturestree = {};
                    angular.forEach(lectures, function(node){
                        var lecturesids = [{id:node.id,type:"lecture"}];
                        var curnode = node;
                        while(true) {
                            var parent = curnode.getParentNode();
                            if (parent == null)
                                break;
                            curnode = parent;
                            lecturesids.push({id:curnode.id,type:"catenate"});
                        }
                        lecturesids.reverse();

                        var curpoint = lecturestree;
                        angular.forEach(lecturesids, function(oo) {
                            if (!curpoint[oo.id]) {
                                curpoint[oo.id] = {
                                    type:oo.type,
                                    children:{}
                                };
                            }
                            curpoint = curpoint[oo.id].children;
                        });
                    });
                    lecturestree = JSON.stringify(lecturestree);

                    $("[name='row[lectures]']").val(lecturestree);
                    return true;
                });
            });
        },
        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});