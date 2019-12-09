define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        indexscape:function($scope, $compile,$timeout){
            var options = {
                extend: {
                    index_url: 'promotion/index',
                    add_url: 'promotion/add',
                    del_url: null,
                    summation_url: 'promotion/summation',
                    table: 'promotion',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __('%s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'promotion/view',
                        extend: 'data-toggle="tooltip"',
                    },
                    {
                        name: 'del',
                        url: 'promotion/del',
                        classname: 'btn btn-xs btn-danger btn-delone',
                        icon: 'fa fa-trash',
                        extend: 'data-toggle="tooltip"',
                        visible:function(row, j){
                            if (typeof Config.admin_branch_model_id == "undefined") {
                                return true;
                            }
                            return Config.admin_branch_model_id == row.branch_model_id;
                        }
                    }
                ]
            };
            Table.api.init(options);
            var table = $("#table-index");

            $scope.genreModelIds = [];
            $scope.classChanged = function(data) {
                var typeIds = [];
                angular.forEach(data.selected, function(id){
                    if ($.isNumeric(id))
                        typeIds.push(id);
                });
                $scope.genreModelIds = typeIds;
                $scope.$broadcast("refurbish");
            };

            $scope.formatterOperate = function(value, row, index) {
                var buttons = Table.api.formatter.operate.call(this,value, row, index);
                return buttons;
            };

            $scope.searchFieldsParams = function(param) {
                param.custom = {};
                if ($scope.genreModelIds.length > 0) {
                    param.custom['genre_cascader_id'] = ["in",$scope.genreModelIds];
                }
                return param;
            };

            $scope.distribute = function(param) {
                Layer.open({
                    title:"选择校区",
                    type: 2,
                    btn: ['确定'],
                    content: "/branch/select",
                    area: ['500px', '350px'],maxmin:false,
                    yes:function(index, layero) {
                        var ids = Table.api.selectedids(table);
                        var branch_model_id = Layer.getChildFrame('body', index).find("#select-branch").selectpicker('val');
                        Backend.api.ajax({url:"distribute/add",
                            data:{
                                row:{
                                    branch_model_id:branch_model_id,
                                    promotion:ids
                                }
                            }
                        });
                        Layer.close(index);
                    }
                });
            };
            $scope.warrant = function(param) {
                Fast.api.open("/group/select?multiple=true&model_type=staff", "选择员工组", {
                    callback: function (res) {
                        var promotions = $.map(table.bootstrapTable('getSelections'), function (row) {
                            return row['id'];
                        });
                        var groups = $.map(res.gs, function (row) {
                            return row['id'];
                        });
                        Backend.api.ajax({url:"warrant/add",
                            data:{
                                row:{
                                    branch_model_id:Config.admin_branch_model_id,
                                    group_model_id:groups,
                                    promotion_model_id:promotions
                                }
                            }
                        });
                    }
                });
            };
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "promotion/index",dataType: 'json',
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

            $scope.openLesson = function() {
                Controller.api.openLesson('/promotion/slideshare/ids/' + $scope.row.id);
                return true;
            };
        },
        scenery: {
            lore:function($scope, $compile,$timeout, data){
                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                        "promotion_model_id":$scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    extend: {
                        del_url: 'lore/del',
                        summation_url: 'lore/summation',
                        table: 'lore',
                    }
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            datum:function($scope, $compile,$timeout, data){
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                var table = $("#table-datum");

                $scope.searchFieldsParams = function(param) {
                    var type = $("#toolbar-datum .btn-group .btn.active").data("id");
                    param.custom = {
                        "datum.promotion_model_id":$scope.row.id,
                        "datum.type":type,
                    };
                    return param;
                };
                Table.api.init({
                    extend: {
                        del_url: 'datum/del',
                        summation_url: 'datum/summation',
                        table: 'datum',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'datum/view'
                        }
                    ]
                });
                $('.btn-group[data-toggle="btn-toggle"]').each(function() {
                    var a = $(this);
                    $(this).find(".btn").on("click", function(b) {
                        a.find(".btn.active").removeClass("active"), $(this).addClass("active"), b.preventDefault();
                        table.bootstrapTable('refresh', {});
                    });
                });
                $("#toolbar-datum .btn-group .btn:first").click();

                $scope.$broadcast("shownTable");

                $(".btn-add").on("click", function(){
                    var type = $("#toolbar-datum .btn-group .btn.active").data("id");
                    var url = "/datum/add?promotion_model_id=" + $scope.row.id + "&type=" + type;
                    Fast.api.open(url, __('Add'), $(this).data() || {});
                    return false;
                });
            },
            induction:function($scope, $compile,$timeout, data){
                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                        "promotion_model_id":$scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    extend: {
                        del_url: 'induction/del',
                        table: 'induction',
                    },
                    buttons : [
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            expound:function($scope, $compile,$timeout, data) {
                $scope.colors = Config.expoundColors;
                $scope.selectnode = null;

                $scope.addCatenate = function() {
                    var url = "/exlecture/add?type=catenate&promotion_model_id=" + $scope.row.id;
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
                            $.ajax({url:"exlecture/classtree",
                                data:{
                                    promotion_model_id:$scope.row.id,
                                    id:$scope.selectnode.id
                                }
                            }).then(function(ret){
                                var tree = $.fn.zTree.getZTreeObj("channeltree");
                                tree.removeChildNodes($scope.selectnode); tree.addNodes($scope.selectnode, 0,ret);
                            });
                        }}
                    );
                    return false;
                };
                $scope.delCatenate = function() {
                    if ($scope.selectnode) {
                        Layer.confirm(
                            __('Are you sure you want to delete this item?'),
                            {icon: 3, title: __('Warning'), shadeClose: true},
                            function (index) {
                                Backend.api.ajax({url:"exlecture/del",
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
                    $("#table-expound").bootstrapTable('refresh');
                    $("div.btn-add-expound-group").toggleClass('hide', !($scope.selectnode.type == "lecture"));
                    $("a.btn-del-warerange").toggleClass('disabled', $scope.selectnode.status == "locked");
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
                        addDiyDom: function(treeId, treeNode) {
                            if (treeNode.type != "lecture") return;

                            var aObj = $("#" + treeNode.tId + "_a");
                            var editStr = "<a id='duration_" +treeNode.id+ "'><img src='/assets/img/yiguoqi.jpg'  style='width: 13px;'/> <span id='duration_value_"+treeNode.id+"'>"+treeNode.duration+"</span>分钟</a>" ;
                            aObj.after(editStr);
                            var btn = $("#duration_"+treeNode.id);
                            if (btn) btn.bind("click", function(){
                                var options = {
                                    value:treeNode.duration,
                                };
                                Layer.prompt(options,function(value, index){
                                    if (!$.isNumeric(value))
                                        return;
                                    Fast.api.ajax({url:"exlecture/multi",
                                        data:{
                                            params:'duration='+value,
                                            ids:treeNode.id
                                        },
                                    }, function (ret, data) {
                                        treeNode.duration = value;
                                        $("#duration_value_" + treeNode.id, btn).html(value);
                                    });
                                    layer.close(index);
                                });

                            });
                        }
                    },
                    callback: {
                        beforeDrag:  function(treeId, treeNodes) {
                            if (treeNodes[0].status == "locked")
                                return false;

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
                        beforeDrop:  function(treeId, treeNodes, targetNode, moveType) {
                            if (targetNode.status == "locked") {
                                var nextNode = targetNode.getNextNode();
                                if (nextNode == null)
                                    return false;
                                var preNode = targetNode.getPreNode();
                                if (preNode == null)
                                    return false;
                            }
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
                            $.ajax({url:"exlecture/weigh",data:{nodes:orderNode}});
                        },
                        onClick: $scope.onTreeClick,
                    }
                };

                $.ajax({
                    url:"exlecture/classtree",
                    data:{
                        promotion_model_id:$scope.row.id
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
                    $('#channeltree').css("height", document.body.offsetHeight - 200);
                });
                var html = $compile(data.content)($scope);
                $scope.$apply(function(){
                    angular.element("#tab-" + $scope.scenery.name).html(html);
                });

                Table.api.init({
                    extend: {
                        index_url: 'expound/index',
                        edit_url: 'expound/edit',
                        del_url: 'expound/del',
                        table: 'expound',
                    },
                    commonSearch: false, //是否启用通用搜索
                    showExport: false,
                    search: false, //是否启用快速搜索
                });
                var table = $("#table-expound");

                window.animationChange = function(sel, id){
                    var value = $(sel).val();
                    $.ajax({url:"expound/multi" ,dataType: 'json', type: "POST",
                        data:{
                            params:'animation_id='+value,
                            ids:id
                        },
                        success: function (data) {
                        }
                    });
                };

                Table.api.events.operate = $.extend(Table.api.events.operate, {
                    'click .btn-editone': function (e, value, row, index) {
                        e.stopPropagation(); e.preventDefault();
                        var options = table.bootstrapTable('getOptions');
                        var url = options.extend.edit_url + '?promotion_model_id=' + $scope.row.id + "&ids=" + row['id'];
                        Layer.full(Backend.api.open(url, __('Edit'), {}));
                    }
                });


                var columns = [
                    [
                        {checkbox: true},
                        {
                            field: 'name', title: __('name'), align: 'left',
                            formatter: function (value, row, index) {
                                var html = [];

                                for (var i in row.detail) {
                                    if (typeof $scope.colors[row.detail[i]['type']] == 'undefined') {
                                        var color =$scope.colors['warehouse']['color'];
                                    } else {
                                        var color =$scope.colors[row.detail[i]['type']]['color'];
                                    }
                                    html.push("<span class='label label-default label-"+row.detail[i]['type']+"' style='font-size: 13px;background-color: "+color+";'>" + row.detail[i]['data'] + "</span>");
                                }
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
                ];

                // 初始化表格
                table.bootstrapTable({
                    toolbar: "#toolbar-expound", //工具栏
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'weigh',
                    columns: columns,
                    queryParams:function (param) {
                        param.custom = {
                            "promotion_model_id": $scope.row.id,
                        };
                        if ($scope.selectnode) {
                            param.custom['exlecture_model_id'] = ($scope.selectnode?$scope.selectnode.id:-1);
                        }
                        return param;
                    }
                });
                Table.api.bindevent(table);

                $(".btn-add-expound").click(function(){
                    if ($scope.selectnode == null || $scope.selectnode.type != "lecture") {
                        Layer.alert('必须选择模板');
                        return false;
                    }
                    var href = $(this).attr("href");
                    var url = href + '&promotion_model_id=' + $scope.row.id + "&exlecture_model_id=" + $scope.selectnode.id;
                    var index = Backend.api.open(url, "新建流程", {
                        moveOut: false,
                        callback: function (res) {
                        }}
                    );
                    Layer.full(index);
                    return false;
                });

                table.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".preset-condition.preset-condition-video", table).on("click", function(){
                        var url = $(this).attr("href");
                        Backend.api.open("/index/player?url=" +url , "播放视频", {});
                        return false;
                    });
                });
            },
            distribute:function($scope, $compile,$timeout, data) {
                var html = $compile(data.content)($scope);
                $scope.$apply(function(){
                    angular.element("#tab-" + $scope.scenery.name).html(html);
                });

                Table.api.init({
                    extend: {
                        index_url: 'distribute/index',
                        table: 'distribute',
                    },
                    commonSearch: false, //是否启用通用搜索
                    showExport: false,
                    search: false, //是否启用快速搜索
                });
                var table = $("#table-distribute");

                // 初始化表格
                table.bootstrapTable({
                    toolbar: "#toolbar-distribute", //工具栏
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'id',
                    columns: [
                        [
                            {
                                field: 'branch.name', title: "校区", align: 'left',
                            }
                        ]
                    ],
                    queryParams:function (param) {
                        param.custom = {
                            "promotion_model_id": $scope.row.id,
                        };
                        return param;
                    },
                });
                Table.api.bindevent(table);
            },
            procedure:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "promotion_id": $scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    sortName: 'weigh',
                    extend: {
                        del_url: 'procedure/del',
                        summation_url: 'procedure/summation',
                        table: 'procedure'
                    },
                    buttons: [
                        {
                            name: 'view',
                            title: function (row, j) {
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'procedure/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");

            },
            middleware:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "promotion_model_id": $scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    extend: {
                        del_url: 'middleware/del',
                        summation_url: 'middleware/summation',
                        table: 'middleware'
                    },
                    buttons: [
                        {
                            name: 'view',
                            title: function (row, j) {
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'middleware/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");

            },
            proar:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "promotion_id": $scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    sortName: 'id',
                    extend: {
                        del_url: 'proar/del',
                        summation_url: 'proar/summation',
                        table: 'proar',
                    },
                    buttons: [
                        {
                            name: 'view',
                            title: function (row, j) {
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'proar/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            repertory:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "promotion_model_id": $scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    sortName: 'id',
                    extend: {
                        del_url: 'repertory/del',
                        summation_url: 'repertory/summation',
                        table: 'repertory',
                    },
                    buttons: [
                        {
                            name: 'view',
                            title: function (row, j) {
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'repertory/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            pattern:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "promotion_id": $scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    sortName: 'id',
                    extend: {
                        del_url: 'pattern/del',
                        summation_url: 'pattern/summation',
                        table: 'pattern',
                    },
                    buttons: [
                        {
                            name: 'view',
                            title: function (row, j) {
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'pattern/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            warrant: function ($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "promotion_model_id": $scope.row.id,
                    };
                    return param;
                };

                Table.api.init({
                    sortName: 'id',
                    extend: {
                        del_url: 'warrant/del',
                        add_url: 'warrant/add',
                        summation_url: 'warrant/summation',
                        table: 'warrant',
                    },
                    buttons: [
                        {
                            name: 'view',
                            title: function (row, j) {
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'warrant/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
        },

        bindevent:function($scope) {
            if (Config.admin_branch_model_id != 0) {
                $('[name="row[genre_cascader_id]"]').attr("disabled","disabled").val($scope.row['genre_cascader_id'] = 46);
                $.each(["templet","class_number"], function(k,v){
                    $('[data-field-name="'+v+'"]').hide().trigger("rate");
                });
            }
            Form.api.bindevent($("form[role=form]"), $scope.submit);
            if (Config.staff != null)$('[data-field-name="branch"]').hide().trigger("rate");
        },

        schedule:function() {
            AngularApp.controller("schedule", function($scope,$timeout) {
                $scope.row = row;
                require(['jquery-ui.min', 'fullcalendar', 'fullcalendar-lang'], function () {
                    var events = {
                        url: "promotion/schedule",
                        data: function () {
                            return {
                                "ids":$scope.row.id
                            };
                        }
                    };
                    $('#calendar').fullCalendar({
                        header: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'month,agendaWeek,agendaDay, listMonth'
                        },
                        dayClick: function (date, jsEvent, view) {
                            //$(this).toggleClass('selected');
                        },
                        eventClick: function (calEvent, jsEvent, view) {
                            var that = this;
                            var status = $(this).hasClass("fc-completed") ? "normal" : "completed";
                            return true;
                        },
                        events: events,
                        navLinks: true,

                        eventAfterAllRender: function (view) {
                            $("a.fc-event[href]").attr("target", "_blank");
                        }
                    });
                });
            });
        },

        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "promotion/statistic",dataType: 'json',cache: false,
                        success: function (ret) {
                            $scope.$apply(function(){
                                $scope.stat = ret.data;
                            });
                        }
                    });
                };
                $scope.$on("refurbish", $scope.refresh);$scope.refresh(); $(".btn-refresh").on("click", $scope.refresh);
            });
        },
        api: {
            openLesson:function(url){
                var index = top.Layer.open({
                    type: 2,
                    title: false,
                    closeBtn: 0,
                    shadeClose: false,
                    area: ['320px', '195px'],
                    maxmin: false,
                    content: url,
                    end: function(){
                        window.focus();
                    }
                });
                top.Layer.full(index);
            }
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});