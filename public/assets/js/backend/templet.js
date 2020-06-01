define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        indexscape:function($scope, $compile,$timeout){
            var options = {
                extend: {
                    index_url: 'templet/index',
                    add_url: 'templet/add',
                    del_url: 'templet/del',
                    summation_url: 'templet/summation',
                    table: 'templet',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __('%s', row.idcode);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'templet/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "templet/index",dataType: 'json',
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
            preset:function($scope, $compile,$timeout, data) {
                $scope.selectnode = null;
                $scope.addCatenate = function() {
                    var url = "/prelecture/add?type=catenate&templet_model_id=" + $scope.row.id;
                    if ($scope.selectnode) {
                        var parentNode = $scope.selectnode.getParentNode();
                        if ($scope.selectnode.type == "catenate") {
                            url += "&pid=" + $scope.selectnode.id;
                            url += "&lecture_id=" + $scope.selectnode.lecture_id;
                        } else {
                            if (parentNode) {
                                url += "&pid=" + parentNode.id;
                                url += "&lecture_id=" + parentNode.lecture_id;
                            }
                        }
                    }
                    Backend.api.open(url, "新分类", {
                        callback: function (res) {
                            $.ajax({url:"prelecture/classtree",
                                data:{
                                    templet_model_id:$scope.row.id,
                                    id:$scope.selectnode.id
                                }
                            }).then(function(ret){
                                var tree = $.fn.zTree.getZTreeObj("channeltree");
                                tree.removeChildNodes($scope.selectnode);
                                tree.addNodes($scope.selectnode, 0,ret);
                            });
                        }}
                    );
                    return false;
                };
                $scope.delCaenate = function() {
                    if ($scope.selectnode) {
                        Layer.confirm(
                            __('Are you sure you want to delete this item?'),
                            {icon: 3, title: __('Warning'), shadeClose: true},
                            function (index) {
                                Backend.api.ajax({url:"prelecture/del",
                                    data:{
                                        ids:$scope.selectnode.id
                                    }
                                }, function(){
                                    var parentNode = $scope.selectnode.getParentNode();
                                    var treeObj = $.fn.zTree.getZTreeObj("channeltree");
                                    treeObj.selectNode(parentNode); treeObj.removeNode($scope.selectnode, true);$scope.onTreeClick(null, "channeltree", parentNode);
                                });
                                Layer.close(index);
                            }
                        );
                    }
                    return false;
                };

                $scope.onTreeClick = function(event, treeId, treeNode, clickFlag) {
                    $scope.selectnode = treeNode;
                    $("#table-preset").bootstrapTable('refresh');
                    $("a.btn-add-preset").toggleClass('disabled', !($scope.selectnode.type == "lecture"));
                    $("a.btn-del-warerange").toggleClass('disabled', $scope.selectnode.lecatenate_id == "0");
                    $("a.btn-slideshare-preset").toggleClass('disabled', !($scope.selectnode.type == "lecture"));
                    if ($scope.selectnode.type == "lecture") {
                        $("a.btn-slideshare-preset").attr("href", '/prelecture/slideshare?ids=' + $scope.selectnode.id)
                    }
                    return true;
                };

                var curDragNodes = null;
                var setting = {
                    edit: {
                        enable: true,
                        showRemoveBtn: false,
                        showRenameBtn: false,
                        drag:{
                            isMove: true,
                            autoExpandTrigger: true,
                            prev: function(treeId, nodes, targetNode) {
                                var pNode = targetNode.getParentNode();
                                if (pNode && pNode.dropInner === false) {
                                    return false;
                                } else {
                                    for (var i=0,l=curDragNodes.length; i<l; i++) {
                                        var curPNode = curDragNodes[i].getParentNode();
                                        if (curPNode && curPNode !== targetNode.getParentNode() && curPNode.childOuter === false) {
                                            return false;
                                        }
                                    }
                                }
                                return true;
                            },
                            inner: function(treeId, nodes, targetNode) {
                                if (targetNode && targetNode.dropInner === false) {
                                    return false;
                                } else {
                                    for (var i=0,l=curDragNodes.length; i<l; i++) {
                                        if (!targetNode && curDragNodes[i].dropRoot === false) {
                                            return false;
                                        } else if (curDragNodes[i].parentTId && curDragNodes[i].getParentNode() !== targetNode && curDragNodes[i].getParentNode().childOuter === false) {
                                            return false;
                                        }
                                    }
                                }
                                return true;
                            },
                            next: function(treeId, nodes, targetNode) {
                                var pNode = targetNode.getParentNode();
                                if (pNode && pNode.dropInner === false) {
                                    return false;
                                } else {
                                    for (var i=0,l=curDragNodes.length; i<l; i++) {
                                        var curPNode = curDragNodes[i].getParentNode();
                                        if (curPNode && curPNode !== targetNode.getParentNode() && curPNode.childOuter === false) {
                                            return false;
                                        }
                                    }
                                }
                                return true;
                            }
                        },
                    },
                    data: {
                        simpleData: {
                            enable: true
                        }
                    },
                    view:{
                        nameIsHTML: true,
                        removeHoverDom:function (treeId, treeNode) {
                            $("#diyBtn_"+treeNode.id).unbind().remove();
                            $("#diyBtn_space_" +treeNode.id).unbind().remove();
                        },
                        addHoverDom: function (treeId, treeNode) {
                            if (treeNode.id == 0)
                                return;
                            var aObj = $("#" + treeNode.tId + "_a");

                            if ($("#diyBtn_"+treeNode.id).length>0) return;
                            var editStr = "<span id='diyBtn_space_" +treeNode.id+ "' >&nbsp;</span><select id='diyBtn_" +treeNode.id+ "'><option value='locked'>锁定</option><option value='normal'>未锁定</option></select>";
                            aObj.after(editStr);
                            $("select#diyBtn_"+treeNode.id+" option[value='"+treeNode.status+"']").prop("selected", "selected");

                            var btn = $("#diyBtn_"+treeNode.id);
                            if (btn) btn.bind("change", function(){
                                var value = $(this).val();
                                $.ajax({url:"prelecture/multi" ,dataType: 'json', type: "POST",
                                    data:{
                                        params:'status='+value,
                                        ids:treeNode.id
                                    },
                                    success: function (data) {
                                        treeNode.status = value;
                                    }
                                });
                            });
                        }
                    },
                    callback: {
                        beforeDrag:  function(treeId, treeNodes) {
                            for (var i=0,l=treeNodes.length; i<l; i++) {
                                if (treeNodes[i].drag === false) {
                                    curDragNodes = null;
                                    return false;
                                } else if (treeNodes[i].parentTId && treeNodes[i].getParentNode().childDrag === false) {
                                    curDragNodes = null;
                                    return false;
                                }
                            }
                            curDragNodes = treeNodes;
                            return true;
                        },
                        onDrop:  function(event, treeId, treeNodes, targetNode, moveType){
                            var orderNode = {};
                            angular.forEach(treeNodes, function(node){
                                var parentNode = node.getParentNode();
                                orderNode[parentNode.id] = [];
                                angular.forEach(parentNode.children, function(n2){
                                    orderNode[parentNode.id].push(n2.id);
                                });
                            });
                            $.ajax({url:"prelecture/weigh",data:{nodes:orderNode}});
                        },
                        onClick: $scope.onTreeClick,
                    }
                };
                $.ajax({
                    url:"prelecture/classtree",
                    data:{
                        templet_model_id:$scope.row.id
                    }
                }).then(function(ret){
                    var root =  {
                        'id':0,
                        'isParent':true,
                        'name'  : "讲解流程",
                        'open'  : true,
                        'childOuter':false,
                        'status':"locked",
                        'type':'catenate',
                        'children':ret,
                        'lecatenate_id':0,
                        'iconSkin':"root"
                    };
                    $.fn.zTree.init($("#channeltree"), setting, root);
                    var treeObj = $.fn.zTree.getZTreeObj("channeltree");
                    var parentNode =  treeObj.getNodeByParam("id", 0, null);
                    treeObj.selectNode(parentNode);$scope.onTreeClick(null, "channeltree", parentNode);
                });

                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));

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

                var table = $("#table-preset");

                window.animationChange = function(sel, id){
                    var value = $(sel).val();
                    $.ajax({url:"preset/multi" ,dataType: 'json', type: "POST",
                        data:{
                            params:'animation_id='+value,
                            ids:id
                        },
                        success: function (data) {
                        }
                    });
                };

                // 初始化表格
                table.bootstrapTable({
                    toolbar: "#toolbar-preset", //工具栏
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'weigh',
                    columns: [
                        [
                            {checkbox: true},
                            {
                                field: 'name', title: __('name'), align: 'left',
                                formatter: function (value, row, index) {
                                    var html = [];
                                    html.push(row.detail.join(''));
                                    return html.join('');
                                }

                            },
                            {
                                field: 'status', title: "动画", align: 'left',
                                formatter: function (value, row, index) {
                                    var select = $("<select onchange='animationChange(this,"+row.id+")'></select>");
                                    for (var i in Config.animations) {
                                        var ani = Config.animations[i];
                                        select.append("<option value='"+ani.id+"' "+(row.animation_id==ani.id?"selected":"")+">"+ani.name+"</option>");
                                    }
                                    return select.prop("outerHTML");
                                }

                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ],
                    queryParams:function (param) {
                        param.custom = {
                            "templet_model_id": $scope.row.id,
                        };
                        param.custom['prelecture_model_id'] = ($scope.selectnode?$scope.selectnode.id:-1);
                        return param;
                    },
                    detailView:true,
                    detailFormatter:function (index, row) {
                        var html = [];

                        return html.join('');
                    }
                });
                // 为表格绑定事件
                Table.api.bindevent(table);


                $(".btn-add-preset").click(function(){
                    if ($scope.selectnode == null || $scope.selectnode.type != "lecture") {
                        Layer.alert('必须选择模板');
                        return false;
                    }
                    var url = '/preset/edit?templet_model_id=' + $scope.row.id + "&prelecture_model_id=" + $scope.selectnode.id.substr(2);
                    var index = Backend.api.open(url, "新建流程", {
                        callback: function (res) {
                        }}
                    );
                    Layer.full(index);
                    return false;
                });
            },
        },

        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});