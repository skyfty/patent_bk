define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree) {
    var Controller = {
        //for index
        lands:{
            index:function($scope, $compile,$timeout, data) {

            }
        },
        indexscape:function($scope, $compile,$timeout){
            //$scope.fieldFormatter =Controller.api.fieldFormatter;

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
                if ($scope.speciesModelIds.length > 0) {
                    param.custom['species_cascader_id'] = ["in",$scope.speciesModelIds];
                }
                if (species_cascader_id) {
                    param.custom['species_cascader_id'] = species_cascader_id;
                }
                return param;
            };
            var options = {
                extend: {
                    index_url: 'procedure/index',
                    add_url: 'procedure/add',
                    del_url: 'procedure/del',
                    multi_url: 'procedure/multi',
                    summation_url: 'procedure/summation',
                    table: 'procedure',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'procedure/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){

            $scope.refreshRow = function(){
                $.ajax({url: "procedure/index",dataType: 'json',
                    data:{
                        custom: {"procedure.id":$scope.row.id}
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
            shuttering: function($scope, $compile,$timeout, data){
                if (data.row.type == "division") {
                    Controller.scenery.division($scope, $compile,$timeout, data);
                } else {
                    $scope.searchFieldsParams = function(param) {
                        param.custom = {procedure_model_id:$scope.row.id};
                        return param;
                    };

                    $scope.formaterColumn = function(j, data) {
                        if (data.field == "file") {
                            data.formatter = function (value, row, index) {
                                if (row['type'] == "image") {
                                    return row['file_text'];
                                } else {
                                    return Table.api.formatter['file'].call(this, value, row, index);
                                }
                            }
                        }
                        return data;
                    };

                    Table.api.init({
                        extend: {
                            index_url: 'shuttering/index',
                            summation_url: 'shuttering/summation',
                            table: 'shuttering',
                        },
                        buttons : [
                            {
                                name: 'view',
                                title: function(row, j){
                                    return __('%s', row.name);
                                },
                                classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                                icon: 'fa fa-folder-o',
                                url: 'shuttering/view'
                            }
                        ]
                    });
                    $scope.fields = data.fields;
                    angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                    $scope.$broadcast("shownTable");
                }

            },
            division:function($scope, $compile,$timeout, data) {
                $scope.selectnode = null;

                $scope.addDivision = function() {
                    var url = "/division/add?procedure_model_id=" + $scope.row.id;
                    Backend.api.open(url, "新章节", {
                        callback: function (res) {
                            $.ajax({url:"division/classtree",
                                data:{
                                    procedure_model_id:$scope.row.id,
                                }
                            }).then(function(ret){
                                var tree = $.fn.zTree.getZTreeObj("channeltree");
                                tree.removeChildNodes($scope.selectnode); tree.addNodes($scope.selectnode, 0,ret);
                            });
                        }}
                    );
                    return false;
                };
                $scope.delDivision = function() {
                    if ($scope.selectnode) {
                        Layer.confirm(
                            __('Are you sure you want to delete this item?'),
                            {icon: 3, title: __('Warning'), shadeClose: true},
                            function (index) {
                                Backend.api.ajax({url:"division/del",
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
                    $("#table-paragraph").bootstrapTable('refresh');
                    $("div.btn-add-division-group").toggleClass('hide', !($scope.selectnode.id != "0"));
                    $("a.btn-del-warerange").toggleClass('disabled', $scope.selectnode.id == "0");
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
                            $.ajax({url:"division/weigh",data:{nodes:orderNode}});
                        },
                        onClick: $scope.onTreeClick,
                    }
                };

                $.ajax({
                    url:"division/classtree",
                    data:{
                        procedure_model_id:$scope.row.id
                    }
                }).then(function(ret){
                    var root =  {
                        'id':0,
                        'isParent':true,
                        'name'  : "流程",
                        'open'  : true,
                        'childOuter':false,
                        'status':"locked",
                        'children':ret,
                        'iconSkin':"root"
                    };
                    $.fn.zTree.init($("#channeltree"), setting, root);
                    var treeObj = $.fn.zTree.getZTreeObj("channeltree");
                    var parentNode =  treeObj.getNodeByParam("id", 0, null);
                    treeObj.selectNode(parentNode);
                    $scope.onTreeClick(null, "channeltree", parentNode);
                    $('#channeltree').css("height", document.body.offsetHeight - 250);
                });
                var html = $compile(data.content)($scope);
                $scope.$apply(function(){
                    angular.element("#tab-" + $scope.scenery.name).html(html);
                });

                Table.api.init({
                    extend: {
                        index_url: 'paragraph/index',
                        edit_url: '',
                        del_url: 'paragraph/del',
                        table: 'paragraph',
                    },
                    commonSearch: false, //是否启用通用搜索
                    showExport: false,
                    search: false, //是否启用快速搜索
                });
                var table = $("#table-paragraph");

                Table.api.events.operate = $.extend(Table.api.events.operate, {
                    'click .btn-editone': function (e, value, row, index) {
                        e.stopPropagation(); e.preventDefault();
                        var options = table.bootstrapTable('getOptions');
                        var url = options.extend.edit_url + '?procedure_model_id=' + $scope.row.id + "&ids=" + row['id'];
                        Layer.full(Backend.api.open(url, __('Edit'), {}));
                    }
                });


                var columns = [
                    [
                        {checkbox: true},
                        {
                            field: 'article.name', title: "内容", align: 'left'
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
                    toolbar: "#toolbar-paragraph", //工具栏
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'weigh',
                    columns: columns,
                    detailView:true,
                    detailFormatter:function(idx, row) {
                        var field = {
                            "type":row['article']['type'],
                            "name":"content",
                        };
                        return  Cosmetic.api.formatter(field, row['article']['content'], row['article']);
                    },
                    queryParams:function (param) {
                        param.custom = {
                            "procedure_model_id": $scope.row.id,
                        };
                        if ( $scope.selectnode) {
                            param.custom['division_model_id'] = $scope.selectnode.id;
                        }
                        return param;
                    }
                });
                Table.api.bindevent(table);

                $(".btn-add-paragraph").click(function(){
                    if ($scope.selectnode == null) {
                        Layer.alert('必须选择模板');
                        return false;
                    }
                    var href = $(this).attr("href");
                    var url = href + '&procedure_model_id=' + $scope.row.id + "&division_model_id=" + $scope.selectnode.id;
                    Backend.api.open(url, "新建流程", {
                        callback: function (res) {
                        }}
                    );
                    return false;
                });
            },
            alternating: function($scope, $compile,$timeout, data){
                $scope.fieldFormatter =Controller.api.fieldFormatter;

                $scope.formaterColumn = function(j, data) {
                    if (data.field == "field") {
                        data.formatter = function (value, row, index) {
                            return row['field_name']?row['field_name']:row['field']['name'];
                        }
                    }
                    return data;
                };

                $scope.searchFieldsParams = function(param) {
                    param.custom = {procedure_model_id:$scope.row.id};
                    return param;
                };

                Table.api.init({
                    extend: {
                        index_url: 'alternating/index',
                        del_url: 'alternating/del',
                        summation_url: 'alternating/summation',
                        table: 'alternating',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'alternating/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
        },
        initParam:[
            'species_cascader_id'],
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

            Form.api.bindevent($("form[role=form]"), $scope.submit);
            require(['selectpage'], function () {
                for (var i in self.initParam) {
                    var param = Backend.api.query(self.initParam[i]);
                    if (param) {
                        $('[name="row[' + self.initParam[i] + ']"]').selectPageDisabled(true);
                    }
                }
            });
            if ($scope.row.species_cascader_id) {
                //$('[data-field-name="species"]').hide().trigger("rate");
            }
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },

        api: {
            fieldFormatter:function(field, data, row) {
                if (field.name == "field" && data['type'] == "custom") {
                    return Controller.api.convertFieldName(data['field_model_id'])
                } else {
                    return Cosmetic.api.formatter(field, data, row);
                }
            }
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});