define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','ztree','sortable'], function ($, Backend, Table, Form, Template,angular, Cosmetic,Ztree,Sortable) {
    var Controller = {
        edit: function () {
            var self = this;
            AngularApp.controller("edit", function($scope,$sce, $compile,$timeout,$parse) {
                $scope.row = row;
                if ($scope.row.creator_model_id) {

                } else {
                    $scope.row['creator_model_id'] = $scope.row['owners_model_id'] = Config.admin_id;
                    $scope.row['branch_model_id'] = Config.staff?Config.staff.branch_model_id:0;
                    $scope.primaryFields = $scope.secondFields = $scope.thirdFields = [];
                }
                var mustamount = Fast.api.query("mustamount");

                $scope.toggleAmountUnit = function(treeId, b, ware) {
                    if (mustamount || ($scope.row[treeId] && $scope.row[treeId]['warehouse'] && $scope.row[treeId]['warehouse']['amount'] != "")) {
                        if (b) {
                            $("div.amount-"+treeId).addClass("hide");
                        } else {
                            $("div.amount-"+treeId).removeClass("hide");
                        }
                        $("div.amount-"+treeId+" .amount-unit").html((ware && ware.model && ware.model.unit_text?ware.model.unit_text:""));
                    }
                };

                $scope.serialize=function(){
                    if ($.fn.zTree.getZTreeObj('primary').getSelectedNodes().length != 1) {
                        Toastr.error("主课件必须设置!!");
                        Controller.api.errorFlash($(".panel-primary"));
                        return false;
                    }

                    var arr = ['primary','second','third','entire'];
                    for(var i in arr){
                        var node = $.fn.zTree.getZTreeObj(arr[i]).getSelectedNodes();
                        if (node.length != 1) {
                            continue
                        }
                        if (mustamount) {
                            var amountNumber = $("[name='row["+arr[i]+"][warehouse][amount]']");
                            if(amountNumber.length > 0) {
                                var amount = amountNumber.val();
                                if (!amount) {
                                    Toastr.error("课件数量必须设置!!");
                                    Controller.api.errorFlash($(".panel-"+arr[i]));amountNumber.focus();
                                    return false;
                                }
                            }
                        }

                        node = node[0];
                        if (node.type == "behavior") {
                            $("[name='row["+arr[i]+"][behavior][id]']").val(node.id);
                            node = node.getParentNode();
                        }
                        if (node.type == "assembly") {
                            $("[name='row["+arr[i]+"][assembly][id]']").val(node.id.substr(2));
                            node = node.getParentNode();
                        }
                        if (node.type == "warehouse") {
                            $("[name='row["+arr[i]+"][warehouse][id]']").val(node.id.substr(2));
                        }

                        var arr2 = ['behavior','assembly'];
                        for(var i2 in arr2){
                            var adjective = $("#"+arr[i] + "-"+arr2[i2] + "-adjective").data('sortable').toArray();
                            $("[name='row["+arr[i]+"]["+arr2[i2]+"][adjective][detail]']").val(adjective.join(","));
                        }
                    };
                };

                $scope.refreshTree = function(treeId, wareid, defsel) {
                    $scope.toggleAmountUnit(treeId, true);;
                    $.ajax({
                        url:"/assembly/classtree",
                        data:{
                            warehouse_model_id:wareid
                        }
                    }).then(function(ret){
                        var treeObj = $.fn.zTree.getZTreeObj(treeId);
                        var nodes = treeObj.getNodes();
                        if (nodes && nodes.length>0) {
                            treeObj.removeNode(nodes[0]);
                        }
                        treeObj.addNodes(null, ret);
                        $scope.toggleAmountUnit(treeId, false, ret);;
                        if (defsel === true) {
                            if (ret['bodyid']){
                                var node = treeObj.getNodeByParam("id", ret['bodyid']);
                                if (node['children'] && node['children'].length > 0) {
                                    node = treeObj.getNodeByParam("id", node['children'][0]['id']);
                                }
                            } else {
                                if (ret['children'] && ret['children'].length > 0) {
                                    var children = ret['children'][0];
                                    if (children['children'] && children['children'].length > 0) {
                                        var node = treeObj.getNodeByParam("id", children['children'][0]['id']);
                                    } else {
                                        var node = treeObj.getNodeByParam("id", children['id']);
                                    }
                                } else {
                                    var node = treeObj.getNodeByParam("id", ret['id']);
                                }
                            }
                            if (node) {
                                treeObj.selectNode(node);$scope.onTreeClick([], treeId, node);
                            }
                        }
                    });
                };

                $scope.clearAssemblyCondition = function(treeId){
                    $scope.toggleAmountUnit(treeId, true);;
                    $scope.row[treeId] = {};
                    var arr2 = ['behavior','assembly'];
                    for(var i2 in arr2){
                        $("#"+treeId+"-"+arr2[i2]+"-data-view").html("")
                        $("#"+treeId+"-"+arr2[i2]+"-adjective").find("li").remove();
                    }
                    assemblyMap[treeId] = null;behaviorMap[treeId] = null;
                };

                $scope.addAssembly = function(treeId) {
                    var url = "/warerange/select?";
                    var promotion_model_id = Fast.api.query("promotion_model_id");
                    if (promotion_model_id) {
                        url += "promotion_model_id=" + promotion_model_id;
                    }
                    if (treeId == "primary") {
                        url += "&ids=" + "19,15";
                    }
                    Backend.api.open(url, "选择课件", {
                        callback: function (res) {
                            $scope.refreshTree(treeId, res[0], true);
                            $scope.clearAssemblyCondition(treeId);
                        }}
                    );
                };

                $scope.deleteAssembly = function(treeId) {
                    Layer.confirm("确实要清除吗？", function (index) {
                        $.fn.zTree.init($("#" + treeId), setting);
                        $scope.clearAssemblyCondition(treeId);
                        Layer.close(index);
                    });
                };

                $scope.refreshCondition = function(treeId, type, id) {
                    var div = $("#"+treeId+"-"+type+"-data-view").html("");
                    var url = "/"+type+"/condition";
                    $.ajax({
                        async: false,
                        url:url,
                        data:{
                            id:id
                        },
                    }).then(function(ret){
                        angular.forEach(ret, function(field){
                            if (field.type == "none" || field.type == "")
                                return;

                            field['raw_name'] = "row["+treeId+"]["+type+"]";
                            field['ng_name'] = treeId+"."+type;
                            var fieldName = field.name.split("/");
                            for (var i in fieldName) {
                                field['raw_name'] += "[" + fieldName[i] + "]";
                                field['ng_name'] +="."+fieldName[i];
                            }
                            var form = $('<div class="form-group field-group-'+treeId +"-"+field.name.replace("/","-")+'" data-type="'+field.type+'"  data-unit="'+(field.unit?field.unit:"")+'"/>');
                            form.append($('<label class="control-label col-xs-12 col-sm-2">'+field.title+'</label>'));
                            var form2 = $('<div class="col-xs-12 col-sm-8" id="'+treeId+'-'+field.name+'-show"/>');

                            var condition = null;
                            if ($scope.row[treeId] && $scope.row[treeId][type]) {
                                condition = $parse("row." + field['ng_name'])($scope);
                            }
                            if (condition != null && id == $scope.row[treeId][type]['id']) {
                                var html = $(Form.formatter[field.type]("edit",field, condition, $scope.row[treeId]));
                            } else {
                                $parse("row." + field['ng_name']).assign($scope, field.defaultvalue);
                                var html = $(Form.formatter[field.type]("add",field, field.defaultvalue, {}));
                            }
                            form2.append($compile(html)($scope));
                            form.append(form2);
                            div.append(form);
                        });
                        Form.api.bindevent($("form[role=form]"), $scope.submit, null, $scope.serialize);
                    });

                    var sortable = $("#"+treeId+"-"+type+"-adjective");

                    $scope.$watch("row."+treeId+".assembly.adjective", function(nv, ov){
                        for(var i in nv) {
                            if (i == "detail")
                                continue;
                            var frc = $("["+treeId+"-adjective-"+i+"]", sortable);
                            var fieldGroup = $("div.field-group-"+treeId+"-adjective-"+i);
                            var fieldType = fieldGroup.data("type");
                            var value = "";
                            if (nv[i]) {
                                var adjectiveInput = $('[name="row['+treeId+'][assembly][adjective]['+i+']"]');
                                if (fieldType == "select") {
                                    value = adjectiveInput.find("option:selected").text();
                                } else {
                                    value = adjectiveInput.val();
                                }
                                var fieldUnit = fieldGroup.data("unit");
                                if (fieldUnit) {
                                    value += fieldUnit;
                                }
                            }
                            if (frc.length > 0) {
                                if (nv[i]) {
                                    frc.attr("data-id", i+"_"+nv[i]).html(value);;
                                } else {
                                    frc.remove();
                                }
                            } else if (nv[i]) {
                                sortable.append($("<li "+treeId + "-adjective-"+i +" data-id='"+i+"_"+nv[i]+"'>" + value + "</li>"));
                            }
                        }
                    }, true);
                };

                $scope.refreshAadjective = function(treeId, type, name,body, id) {
                    var sortable = $("#"+treeId+"-"+type+"-adjective");
                    sortable.find("li").remove();
                    var model = null;
                    if ($scope.row[treeId]) {
                        model = $scope.row[treeId][type];
                    }
                    var detail = model && model['adjective']?model['adjective']['detail']:"";
                    if (detail != "" && model && id == model['id']) {
                        detail = detail.split(",");
                        for (var i2 in detail) {
                            var pos = detail[i2].indexOf("_");
                            var field = pos != -1?detail[i2].substr(0, pos):detail[i2];
                            var adjname = model['adjective_list'][i2]['data'];
                            var lidiv = $("<li data-id='"+detail[i2]+"' "+treeId + "-adjective-" + field+">" + adjname + "</li>");
                            sortable.append(lidiv);
                        }
                    }
                    else{
                        if (body) {
                            name = $.fn.zTree.getZTreeObj(treeId).getNodes()[0].name;
                            $("[name='row["+treeId+"][warehouse][amount]']").trigger("change");
                        }
                        sortable.append($("<li data-id='self'>" + name + "</li>"));
                    }
                };

                $scope.refreshAttributeState = function(treeId, treeNode, id) {
                    $scope.refreshCondition(treeId, treeNode.type, id);
                    $scope.refreshAadjective(treeId,treeNode.type, treeNode.name, treeNode.body, id);
                };

                var assemblyMap = {},behaviorMap = {};

                $scope.onTreeClick = function(event, treeId, treeNode, clickFlag){
                    if (treeNode.type == "behavior") {
                        var parentNode = treeNode.getParentNode();
                        if (assemblyMap[treeId] != parentNode.id) {
                            assemblyMap[treeId] = parentNode.id;
                            $scope.refreshAttributeState(treeId, parentNode, parentNode.id.substr(2));
                        }

                        if (behaviorMap[treeId] != treeNode.id) {
                            $scope.refreshAttributeState(treeId, treeNode, behaviorMap[treeId] = treeNode.id);
                        }

                    }else if (treeNode.type == "assembly") {
                        if (assemblyMap[treeId] != treeNode.id) {
                            assemblyMap[treeId] = treeNode.id;
                            behaviorMap[treeId] = null;
                            $("#"+treeId+"-behavior-data-view").html("");
                            $scope.refreshAttributeState(treeId, treeNode, treeNode.id.substr(2));
                            var warehouseAmount = $("[name='row["+treeId+"][warehouse][amount]']");
                            warehouseAmount.unbind("change");
                            if (treeNode.body) {
                                var sortable = $("#"+treeId+"-assembly-adjective");
                                warehouseAmount.bind("change", function(){
                                    var value = $(this).val();
                                    var valuehtml = value;
                                    var fieldUnit = $(".amount-" + treeId + " .amount-unit").html();
                                    if (fieldUnit) {
                                        valuehtml += fieldUnit;
                                    }
                                    var frc = $("["+treeId+"-adjective-amount]", sortable);
                                    if (frc.length > 0) {
                                        if (value) {
                                            frc.attr("data-id", "amount_"+value).html(valuehtml);;
                                        } else {
                                            frc.remove();
                                        }
                                    } else if (value) {
                                        var self = $("[data-id='self']", sortable);
                                        self.before($("<li "+treeId + "-adjective-amount data-id='"+"amount_"+value+"'>" + valuehtml + "</li>"));
                                    }
                                });
                            }
                        }
                    }
                };

                $scope.onTreeCreated = function(event, treeId, treeNode) {
                    var zTree = $.fn.zTree.getZTreeObj(treeId);
                    var mobject = $scope.row[treeId];
                    if (!mobject)
                        return true;
                    if (treeNode.type == "warehouse") {
                        if (mobject['behavior'] && mobject['behavior']['id']) {
                            var node = zTree.getNodeByParam("id", mobject['behavior']['id']);
                            if (node != null) {
                                zTree.selectNode(node);$scope.onTreeClick(event, treeId, node);
                            }
                        }else if (mobject['assembly'] && mobject['assembly']['id']) {
                            var node = zTree.getNodeByParam("id", "a_"+mobject['assembly']['id']);
                            if (node != null) {
                                zTree.selectNode(node);$scope.onTreeClick(event, treeId, node);
                            }
                        }

                        var warehouseAmount = $("[name='row["+treeId+"][warehouse][amount]']");
                        if ($scope.row[treeId]['warehouse']) {
                            warehouseAmount.val($scope.row[treeId]['warehouse']['amount']);
                        }
                    }
                };

                var setting = {
                    data: {
                        simpleData: {
                            enable: true
                        }
                    },
                    callback: {
                        onClick:$scope.onTreeClick,
                        onNodeCreated: $scope.onTreeCreated
                    }
                };
                $.fn.zTree.init($("#primary"), setting);$.fn.zTree.init($("#second"), setting); $.fn.zTree.init($("#third"), setting); $.fn.zTree.init($("#entire"), setting);

                var arr = ["primary", "second", "third",'entire'];
                for(var i in arr) {
                    var mobject = $scope.row[arr[i]];
                    if (mobject && mobject['warehouse']['id']) {
                        $scope.refreshTree(arr[i], mobject['warehouse']['id']);
                    }
                }
                Form.api.bindevent($("form[role=form]"), $scope.submit, null, $scope.serialize);
            });
        },
        bindevent:function($scope) {
        },
        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});