define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        lands:{
            customer: function($scope, $compile,$timeout, data) {
                $scope.$watch("wecont", function(n,o){
                    if (n!=o) {
                        $scope.$broadcast("refurbish");
                    }
                });

                $scope.searchFieldsParams = function(param) {
                    param.custom = {"reckon_type":"customer"};

                    if ($scope.branch_model_id) {
                        param.custom['customer.branch_model_id'] = $scope.branch_model_id;
                    }
                    if ($scope.reckonIds.length > 0) {
                        param.custom['cheque_model_id'] = ["in",$scope.reckonIds];
                    }
                    return param;
                };
            },

            staff: function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {"reckon_type":"staff"};

                    if ($scope.branch_model_id) {
                        param.custom['staff.branch_model_id'] = $scope.branch_model_id;
                    }
                    if ($scope.reckonIds.length > 0) {
                        param.custom['cheque_model_id'] = ["in",$scope.reckonIds];
                    }
                    return param;
                };
            },

            branch: function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {"reckon_type":"branch"};

                    if ($scope.branch_model_id) {
                        param.custom['reckon_model_id'] = $scope.branch_model_id;
                    }
                    if ($scope.reckonIds.length > 0) {
                        param.custom['cheque_model_id'] = ["in",$scope.reckonIds];
                    }
                    return param;
                };
            },

            flow: function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {"reckon_type":"flow"};

                    if ($scope.reckonIds.length > 0) {
                        param.custom['cheque_model_id'] = ["in",$scope.reckonIds];
                    }
                    return param;
                };
            },
            payconfirm: function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {"reckon_type":"payconfirm"};

                    if ($scope.reckonIds.length > 0) {
                        param.custom['cheque_model_id'] = ["in",$scope.reckonIds];
                    }
                    return param;
                };

                $scope.confirm = function() {
                    var table = $("#table-payconfirm");
                    var ids = Table.api.selectedids(table);
                    Layer.confirm(
                        __('确认要完成选择的 %s 账目吗？', ids.length),
                        {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function (index) {
                            Fast.api.ajax({
                                url:"/account/confirm",
                                data:{
                                    ids:ids
                                }
                            }, function(){
                                $scope.$broadcast("refurbish");
                            });
                            Layer.close(index);
                        }
                    );
                };
            },
            chart: function($scope, $compile,$timeout, data) {
                $("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
            }
        },

        summation:function(params, field) {
            var deferred = $.Deferred();
            var url = 'account/summation/scenery/' + Config.actionname + "/field/" + field;
            $.ajax({url: url,data:params, }).then(function(content) {
                deferred.resolve("合计: ￥" + content.data);
            });
            return deferred;
        },

        indexscape:function($scope, $compile,$timeout){
            var self = this;
            var buttons = [
                {
                    name: 'view',
                    title: function(row, j){
                        return __('%s', row.idcode);
                    },
                    classname: 'btn btn-xs  btn-success btn-magic btn-dialog',
                    icon: 'fa fa-folder-o',
                    url: 'account/view/scenery/' + Config.actionname
                }
            ];

            var options = {
                extend: {
                    index_url: 'account/index/scenery/' + Config.actionname,
                    add_url: 'account/add/scenery/' + Config.actionname,
                    del_url: 'account/del/scenery/' + Config.actionname,
                    multi_url: 'account/multi/scenery/' + Config.actionname,
                    summation_url: self.summation,
                    table: 'account'
                },
                buttons :buttons
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));

            $scope.reckonIds = [];

            $scope.chequeChanged = function(data) {
                var reckonIds = [];
                angular.forEach(data.selected, function(id){
                    if ($.isNumeric(id))
                        reckonIds.push(id);
                });
                $scope.reckonIds = reckonIds;
                $scope.$broadcast("refurbish");
            };

            $scope.stat = {};
            $scope.refresh = function(){
                $.ajax({url: "account/statistic",dataType: 'json',cache: false,
                    success: function (ret) {
                        $scope.$apply(function(){
                            $scope.stat = ret.data;
                        });
                    }
                });
            };
            $scope.$on("refurbish", $scope.refresh);$scope.refresh(); $(".btn-refresh").on("click", $scope.refresh);

            require(['bootstrap-select', 'bootstrap-select-lang'], function () {
                $('#type').selectpicker().on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                    $scope.$broadcast("refurbish");
                }).selectpicker('val', "main");;
            });
            if (Config.staff) $('#branch-filter').hide();
        },

        defaultAction:function(){
            this.index();
        },

        addController:function($scope, $compile,$timeout) {
            var defer = $.Deferred();
            $scope.fields = Config.scenery.fields;
            $scope.row = {};
            $scope.row['branch_model_id'] = Config.staff?Config.staff.branch_model_id:0;
            $scope.row['owners_model_id'] = Config.admin_id;

            $scope.submit = function(form, data, ret){
                Backend.api.close(data);
            };
            var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
            $timeout(function(){
                $("#data-view").html($compile(html)($scope));
                $timeout(function(){
                    $('[data-field-name="reckon_type"]').hide();
                    defer.resolve();
                });
            });
            return defer;
        },

        add: function () {
            var self = this;
            AngularApp.controller("add", function($scope,$compile,$timeout) {
                self.addController($scope, $compile,$timeout).then(function(){
                    self.bindevent($scope);
                });
            });
        },
        deposit: function () {
            var self = this;
            AngularApp.controller("add", function($scope, $compile,$timeout) {
                self.addController($scope, $compile,$timeout).then(function(){
                    self.bindevent($scope);
                });
            });
        },
        withdraw: function () {
            var self = this;
            AngularApp.controller("add", function($scope, $compile,$timeout) {
                self.addController($scope, $compile,$timeout).then(function(){
                    var cheque = null;
                    $('[name="row[cheque_model_id]"]').data("e-params",function(){
                        var param = {};
                        param.custom = {
                            "reckon_table": $scope.row['reckon_type']
                        };
                        var mold = Fast.api.query("mold");
                        if (mold) {
                            param.custom['mold'] = mold;
                        }
                        return param;
                    }).data("e-selected", function(data){
                        cheque = data.row;
                        if (cheque.inflow_table) {
                            $('[name="row[inflow_model_id]"]').selectPageDataUrl(cheque.inflow_table + "/index");
                            $('[data-field-name="inflow"]').show().trigger("rate");
                        } else {
                            $('[data-field-name="inflow"]').hide().trigger("rate");
                        }
                    });

                    var cheque_model_id = Fast.api.query("cheque_model_id");
                    if (cheque_model_id) {
                        $('[name="row[cheque_model_id]"]').attr("disabled","disabled").val($scope.row['cheque_model_id'] = cheque_model_id);
                    };

                    var reckon_model_id = Fast.api.query("reckon_model_id");
                    if (reckon_model_id) {
                        $('[name="row[reckon_model_id]"]').attr("disabled","disabled").val($scope.row['reckon_model_id'] = reckon_model_id);
                    }

                    $('[name="row[reckon_type]"]').change(function(){
                        cheque = null;
                        angular.forEach(['cheque_model_id','inflow_model_id'], function(i){
                            $('[name="row['+i+']"]').selectPageClear();
                        });
                        var url = $(this).val() + "/index";
                        $('[name="row[reckon_model_id]"]').selectPageDataUrl(url);
                    });
                    if ($scope.row && $scope.row['reckon_type']) {
                        $('[name="row[reckon_model_id]"]').data("source", $scope.row['reckon_type'] + "/index");
                    } else {
                        $scope.row['reckon_type'] = Fast.api.query("reckon_type");
                        if ($scope.row['reckon_type']) {
                            $('[name="row[reckon_type]"]').attr("disabled","disabled").val($scope.row['reckon_type']);
                        } else {
                            $scope.row['reckon_type'] = $('[name="row[reckon_type]"]').val();
                        }
                        $('[name="row[reckon_model_id]"]').data("source", $scope.row['reckon_type'] + "/index");
                    }
                    $('[name="row[inflow_model_id]"]').data("e-params",function(){
                        var param = {custom:{}};
                        return param;
                    });
                    setTimeout(function(){$('[data-field-name="inflow"]').hide().trigger("rate");;},200);

                    Form.api.bindevent($("form[role=form]"), $scope.submit);
                });
            });
        },
        bindevent:function($scope){
            var cheque = null, related = null;
            $('[name="row[cheque_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                    "reckon_table": $scope.row['reckon_type']
                };
                var mold = Fast.api.query("mold");
                if (mold) {
                    param.custom['mold'] = mold;
                }
                return param;
            }).data("e-selected", function(data){
                cheque = data.row;
                if (cheque.related_table) {
                    $('[data-field-name="related"]').show().trigger("rate");
                    $('[name="row[related_model_id]"]').selectPageDataUrl(cheque.related_table + "/index");
                } else {
                    $('[data-field-name="related"]').hide().trigger("rate");
                }

                if (cheque.inflow_table && $.inArray(cheque.inflow_table, ['flow','bursar']) == -1) {
                    $('[name="row[inflow_model_id]"]').selectPageDataUrl(cheque.inflow_table + "/index");
                    $('[data-field-name="inflow"]').show().trigger("rate");
                } else {
                    $('[data-field-name="inflow"]').hide().trigger("rate");
                }
            });

            var cheque_model_id = Fast.api.query("cheque_model_id");
            if (cheque_model_id) {
                $('[name="row[cheque_model_id]"]').attr("disabled","disabled").val($scope.row['cheque_model_id'] = cheque_model_id);
            };

            $('[name="row[related_model_id]"]').data("e-selected", function(data){
                related = data.row;

                if (typeof(related['price']) != 'undefined') {
                    $('[name="row[money]"]').val($scope.row['money'] = related['price']);
                }

                if (cheque.inflow_table) {
                    if (cheque.inflow_table != "branch") {
                        $('[data-field-name="inflow"]').show().trigger("rate");
                        $('[name="row[inflow_model_id]"]').val(related[cheque.inflow_table+'_model_id']).selectPageRefresh();
                    } else {
                        $('[name="row[inflow_model_id]"]').val($scope.row['branch_model_id'] = Config.admin_branch_model_id).selectPageRefresh();
                    }
                }
            });
            var related_type = Fast.api.query("related_type");
            if (related_type) {
                $scope.row['related_type'] = related_type;
            }
            if ($scope.row && $scope.row['related_type']) {
                $('[name="row[related_model_id]"]').data("source", $scope.row['related_type'] + "/index");
            } else {
                setTimeout(function(){
                    $('[data-field-name="related"]').hide().trigger("rate");
                },200);
            }
            var related_model_id = Fast.api.query("related_model_id");
            if (related_model_id) {
                $('[name="row[related_model_id]"]').attr("disabled","disabled").val($scope.row['related_model_id'] = related_model_id);
            }

            var reckon_model_id = Fast.api.query("reckon_model_id");
            if (reckon_model_id) {
                $scope.row['reckon_model_id'] = reckon_model_id;
                $('[name="row[reckon_model_id]"]').attr("disabled","disabled").val($scope.row['reckon_model_id']);
            }

            $('[name="row[reckon_type]"]').change(function(){
                cheque = null, related = null;
                angular.forEach(['cheque_model_id','related_model_id','inflow_model_id'], function(i){
                    $('[name="row['+i+']"]').selectPageClear();
                });
                var url = $(this).val() + "/index";
                $('[name="row[reckon_model_id]"]').selectPageDataUrl(url);
            });
            if ($scope.row && $scope.row['reckon_type']) {
                $('[name="row[reckon_model_id]"]').data("source", $scope.row['reckon_type'] + "/index");
            } else {
                $scope.row['reckon_type'] = Fast.api.query("reckon_type");
                if ($scope.row['reckon_type']) {
                    $('[name="row[reckon_type]"]').attr("disabled","disabled").val($scope.row['reckon_type']);
                } else {
                    $scope.row['reckon_type'] = $('[name="row[reckon_type]"]').val();
                }
                $('[name="row[reckon_model_id]"]').data("source", $scope.row['reckon_type'] + "/index");
            }
            if ($.inArray($scope.row['reckon_type'], ['flow','bursar']) != -1) {
                setTimeout(function(){
                    $('[data-field-name="reckon"]').hide().trigger("rate");
                },200);
            }
            $('[name="row[inflow_model_id]"]').data("e-params",function(){
                var param = {custom:{}};
                if (related) {
                    param.custom[cheque['related_table'] + "_ids"] = ["FIND_IN_SET",related["id"]];
                }
                return param;
            });
            setTimeout(function(){$('[data-field-name="inflow"]').hide().trigger("rate");;},200);

            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },
        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});