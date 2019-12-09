define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic', 'moment','bootstrap-datetimepicker'], function ($, Backend, Table, Form, Template,angular, Cosmetic, moment, datepicker) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            },
            calendar:function($scope, $compile,$timeout, data) {
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                var events = {
                    url: "provider/calendar",

                };
                var resources = [];
                angular.forEach(data.classrooms, function(v){
                    resources.push({id: v.id, title: v.name+"," + v.idcode});
                });
                require(['jquery-ui.min','scheduler', 'fullcalendar-lang'], function () {
                    $('#calendar').fullCalendar({
                        groupByResource: true,
                        defaultView: 'timelineDay',
                        resourceLabelText: '教室',
                        header: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'timelineDay,timelineWeek,timelineMonth'
                        },
                        resources: resources,
                        navLinks: true,
                        events:events,
                    });


                });
            }
        },
        indexscape:function($scope, $compile,$timeout){
            var dataTable = $("[ui-formidable]");

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

            $scope.$on('shownTab', function(event,data) {
                $scope.$broadcast("shownTable");
            });

            $scope.searchFieldsParams = function(param) {
                param.custom = {};
                if ($scope.genreModelIds.length > 0) {
                    param.custom['genre_cascader_id'] = ["in",$scope.genreModelIds];
                }
                var branchSelect = $('[name="branch_select"]');
                if (branchSelect.data("selectpicker")) {
                    var branchIds = branchSelect.selectpicker('val');
                    if (branchIds && branchIds.length > 0) {
                        param.custom['branch_model_id'] = ["in", branchIds];
                    }
                }
                return param;
            };

            $scope.tableExtendButtons = function(param) {
                return param;
            };

            $scope.signin = function() {
                var that = this;
                var ids = Table.api.selectedids(dataTable);
                Layer.confirm(
                    __('确认要签到 %s 个课程订单吗?', ids.length), {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                    function (index) {
                        Table.api.multi("signin", ids, dataTable, that);
                        Layer.close(index);
                    }
                );
            };


            $scope.accomplish = function() {
                var that = this;
                var ids = Table.api.selectedids(dataTable);
                Layer.confirm(
                    __('确认要完成 %s 个课程订单吗?', ids.length), {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                    function (index) {
                        Table.api.multi("accomplish", ids, dataTable, that);
                        Layer.close(index);
                    }
                );
            };

            $scope.recover = function() {
                var that = this;
                var ids = Table.api.selectedids(dataTable);
                Layer.confirm(
                    __('确认要恢复 %s 个课程订单吗?', ids.length), {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                    function (index) {
                        Table.api.multi("recover", ids, dataTable, that);
                        Layer.close(index);
                    }
                );
            };

            $scope.changestaff = function() {
                var that = this;
                var ids = Table.api.selectedids(dataTable);
                Fast.api.open("provider/changestaff?hidec=0&ids="+ids, "修改老师", {
                    callback: function (data) {
                        $scope.$apply(function(){
                            dataTable.bootstrapTable('refresh', {});
                        });
                    }
                });
            };

            $scope.changeappointtime = function() {
                var that = this;
                var ids = Table.api.selectedids(dataTable);
                Fast.api.open("provider/changeappointtime?ids="+ids, "调整时间", {
                    callback: function (data) {
                        $scope.$apply(function(){
                            dataTable.bootstrapTable('refresh', {});
                        });
                    }
                });
            };
            $scope.reproduce = function() {
                var that = this;
                var ids = Table.api.selectedids(dataTable);
                Fast.api.open("provider/reproduce?ids="+ids, "克隆订单", {
                    callback: function (data) {
                        $scope.$apply(function(){
                            dataTable.bootstrapTable('refresh', {});
                        });
                    }
                });
            };
            var options = {
                extend: {
                    index_url: 'provider/index',
                    add_url: 'provider/add',
                    del_url: 'provider/del',
                    multi_url: 'provider/multi',
                    summation_url: 'provider/summation',
                    signin_url:'provider/signin',
                    accomplish_url:'provider/accomplish',
                    recover_url:'provider/recover',
                    table: 'provider',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __('%s', row.idcode);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'provider/view'
                    },
                    {
                        name: 'evaluate',
                        title: function(row, j){
                            return __('%s', row.idcode);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-dialog',
                        icon: 'fa fa-commenting',
                        url: 'provider/evaluate'
                    },
                    {
                        name: 'qrcode',
                        title: function(row, j){
                            return __(' %s课评', row.idcode);
                        },
                        classname: 'btn btn-xs btn-success btn-dialog',
                        icon: 'fa  fa-qrcode',
                        url: 'provider/qrcode',
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));

            $timeout(function(){
                // 处理选中筛选框后按钮的状态统一变更
                dataTable.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                    var currow = null;
                    var rows = dataTable.bootstrapTable('getSelections');
                    for(var i in rows) {
                        if (currow == null) {
                            currow = rows[i];
                            continue;
                        }
                        if (currow.branch_model_id != rows[i].branch_model_id ||
                            currow.classroom_model_id != rows[i].classroom_model_id ||
                            currow.appoint_course != rows[i].appoint_course||
                            currow.customer_model_id != rows[i].customer_model_id){
                            currow = null;
                            break;
                        }
                    }

                    if (!currow) {
                        $(".btn-reproduce").toggleClass('disabled', true);
                    }
                });
            }, 200);
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                var defer = $.Deferred();
                $.ajax({url: "provider/index",dataType: 'json',
                    data:{
                        custom:{
                            "provider.id":$scope.row.id
                        }
                    },
                    success: function (data) {
                        if (data && data.rows && data.rows.length == 1) {
                            $scope.$apply(function(){
                                $parse("row").assign($scope, data.rows[0]);
                            });
                            defer.resolve(data.rows);
                        }
                    }
                });
                return defer;
            };
            $(".btn-ajax").data("success", $scope.refreshRow);

            $scope.overstate = function() {
                var ret = $.inArray($scope.row.state, ["0","5","6"]) != -1;
                return ret;
            };
        },
        scenery: {

        },
        initParam:[
            'customer_model_id',
            'branch_model_id',
            'package_model_id',
            'appoint_promotion_model_id',
            'appoint_time','appoint_course','classroom_model_id','staff_model_id','period_model_id'],

        addController:function($scope,$sce, $compile,$timeout) {
            var self = this;
            var defer = $.Deferred();
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
                    self.bindevent($scope, $timeout, defer);
                });
            });
            return defer;
        },

        add: function () {
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                Controller.addController($scope,$sce, $compile,$timeout).then(function(ret){
                    Backend.api.close(ret);
                });
            });
        },

        qrcode: function () {

        },


        increase: function () {
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout){
                Controller.addController($scope,$sce, $compile,$timeout).then(function(ret){
                    Backend.api.close(ret);
                });
            });
        },


        reproduce: function () {
            var self = this;
            AngularApp.controller("reproduce", function($scope,$sce, $compile,$timeout){
                $scope.row = row;
                $('[name="customer_model_ids"]').data("e-params",function(){
                    var param = {};
                    param.custom = {"branch_model_id":$scope.row.branch_model_id};
                    return param;
                });

                $timeout(function(){
                    Form.api.bindevent($("form[role=form]"), function(data, ret){
                        Backend.api.close(ret);
                    });
                });
            });
        },
        enableAppointModel: function(state) {
            if (state) {
                $('[data-field-name="appoint_time"]').show().trigger("rate");
                $('[data-field-name="appoint_course"]').show().trigger("rate");
                $('[data-field-name="classroom"]').show().trigger("rate");
                $('[data-field-name="period"]').show().trigger("rate");
                $('[data-field-name="staff"]').show().trigger("rate");
            } else {
                $('[data-field-name="appoint_time"]').hide().trigger("rate");
                $('[data-field-name="appoint_course"]').hide().trigger("rate");
                $('[data-field-name="classroom"]').hide().trigger("rate");
                $('[data-field-name="staff"]').hide().trigger("rate");
                $('[data-field-name="period"]').hide().trigger("rate");
            }
        },
        bindevent:function($scope,$timeout, defer){
            var self = this;
            $('[name="row[package_model_id]"]').change(function(){
                $('[name="row[appoint_promotion_model_id]"]').selectPageClear();
            }).data("e-params",function(){
                var param = {};
                param.custom = {"branch_model_id":['in',[$scope.row.branch_model_id,0]]};
                return param;
            }).data("e-selected", function(data){
                $scope.package = data.row;
            });
            $('[name="row[genre_cascader_id]"]').on("change", function(){
                $('[name="row[appoint_promotion_model_id]"]').selectPageClear();
            });

            $('[name="row[customer_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {"branch_model_id":$scope.row.branch_model_id};
                return param;
            });

            $('[name="row[staff_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {"branch_model_id":$scope.row.branch_model_id};
                return param;
            });

            $('[name="row[branch_model_id]"]').on("change", function(){
                $('[name="row[customer_model_id]"]').selectPageClear();
                $('[name="row[appoint_promotion_model_id]"]').selectPageClear();
                $('[name="row[classroom_model_id]"]').selectPageClear();
                $('[name="row[staff_model_id]"]').selectPageClear();
                $('[name="row[period_model_id]"]').selectPageClear();
            });

            $('[name="row[appoint_promotion_model_id]"]').data("e-params",function(){
                var param = {"branch_model_id":$scope.row.branch_model_id};;
                param.custom ={};
                if (typeof $scope.package != "undefined") {
                    param["package_id"] =  $scope.package.id;
                }
                if ($scope.row.genre_cascader_id) {
                    param.custom["genre_cascader_id"] = ['in', $scope.row.genre_cascader_id];
                }
                param['orderBy'] = [
                    ['class_number','asc']
                    ['genre_cascader_id','asc']
                ];
                return param;
            }).on('change', function(){
                var val = $(this).val();
                if (!$scope.pre.appoint_time) {
                    self.enableAppointModel(val != "");
                    if (val != "") {
                        var appoint_time = $('[name="row[appoint_time]"]').val();
                        if (appoint_time) {
                            Controller.ableclassroom($scope, appointTime, appoint_time, true);
                        }
                    }
                }
            }).data('formatItem', function(data){
                return "[" + data.row.class_number + "] " + data.name;
            });

            $('[data-field-name="appoint_promotion"] .btn-model').on("click", function(){
                $('[name="row[appoint_promotion_model_id]"]').isValid(function(v){
                    if (v) {
                        Backend.api.open("promotion/schedule/ids/" + $scope.row.appoint_promotion_model_id, "slkdjf", {});
                    }
                });
            });

            $('[name="row[classroom_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {'branch_model_id':$scope.row.branch_model_id};
                return param;
            }).data("e-selected", function(data){
                $('[data-field-name="staff"]').show().trigger("rate");
            }).data("e-clear", function(data){
                $('[name="row[staff_model_id]"]').selectPageClear(); $('[data-field-name="staff"]').hide().trigger("rate");
            }).data('formatItem', function(data){
                var name = data.name;
                if ($scope.unablerooms) {
                    if ($.inArray(data.id,$scope.unablerooms) != -1) {
                        name += "[<span style='color:red'>已占用<span>]";
                    }
                }
                return name;
            });

            $scope.periodChange = function(data){
                if (!$scope.ableclassroom || !$scope.currmonth) {
                    return;
                }
                $scope.unablerooms = [];

                var courses = $scope.ableclassroom[$scope.currmonth];
                if (!courses) return;
                var classrooms = courses[data.row.course];
                for(var cr in classrooms) {
                    if (classrooms[cr]['state'] == false) {
                        $scope.unablerooms.push(classrooms[cr]['classroom_model_id']);
                    }
                }
                $('[name="row[classroom_model_id]"]').selectPageClear();
            };

            $scope.disabledCourse = [];
            $('[name="row[period_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                    'branch_model_id':$scope.row.branch_model_id,
                };
                if ($scope.disabledCourse.length > 0) {
                    param.custom['course'] = ["not in", $scope.disabledCourse];
                }
                return param;
            }).data("e-selected", function(data){
                if (!$scope.pre.period_model_id) {
                    $('[name="row[appoint_course]"]').val(data.row.course);
                    $scope.periodChange(data);
                }
            }).data("e-clear", function(data){
                if (!$scope.pre.period_model_id) {
                    $('[name="row[appoint_course]"]').val("");
                    $scope.periodChange(data);
                }
            });

            var appointTime = $('[data-field-name="appoint_time"] .datetimepicker');
            appointTime.on('dp.update', function (e) {
                if (!$scope.pre.classroom_model_id && e.change == "M") {
                    Controller.ableclassroom($scope, appointTime, e.date.format("YYYY-MM-DD"));
                }
            }).on('dp.change', function (e) {
                if (!$scope.pre.classroom_model_id) {
                    Controller.ableclassroom($scope, appointTime, e.date.format("YYYY-MM-DD"));
                }
            }).on('dp.show', function (e) {
                if (!$scope.pre.classroom_model_id) {
                    Controller.ableclassroom($scope, appointTime, $('[name="row[appoint_time]"]').val());
                }
            });

            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                if (defer) {
                    defer.resolve(data);
                } else {
                    $scope.submit(data, ret);
                }
            });

            require(['selectpage'], function () {
                for (var i in self.initParam) {
                    var param = Backend.api.query(self.initParam[i]);
                    if (param) {
                        if (self.initParam[i] == "appoint_time") {
                            $('[name="row[' + self.initParam[i] + ']"]').attr("readonly", "readonly");
                            self.enableAppointModel(true);
                        } else {
                            $('[name="row[' + self.initParam[i] + ']"]').selectPageDisabled(true);
                            if (self.initParam[i] == "appoint_promotion_model_id") {
                                $('[data-field-name="genre"]').hide().trigger("rate");
                            }
                        }
                    }
                }

                var hidec = Backend.api.query("hidec");
                if (hidec != 0 && !$scope.pre.appoint_promotion_model_id && !$scope.pre.appoint_time && !$scope.pre.period_model_id) {
                    self.enableAppointModel(false);
                }
            });
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },

        refreshAppointCourse:function($scope, cc){
            var datetimePicker = $('[data-field-name="appoint_time"] .datetimepicker');
            var disabledDates = [];
            for(var at in $scope.ableclassroom) {
                var appoint_times = $scope.ableclassroom[at];
                if (appoint_times.state == false) {
                    disabledDates.push(at);
                }
            }
            if (disabledDates.length > 0) {
                datetimePicker.datetimepicker({'disabledDates':disabledDates});
            }

            $scope.disabledCourse = [];
            if ($scope.ableclassroom) {
                if ($scope.ableclassroom[cc]) {
                    for(var at in $scope.ableclassroom[cc]) {
                        var course = $scope.ableclassroom[cc][at];
                        if (course['state'] == false) {
                            $scope.disabledCourse.push(at);
                        }
                    }
                }
            }
        },

        ableclassroom: function($scope, datetimePicker, month, force){
            if (!force && $scope.currmonth && $scope.currmonth == month) {
                Controller.refreshAppointCourse($scope, month);
                return;
            }
            $scope.currmonth = month;

            $.ajax({url: "provider/ableclassroom",dataType: 'json',cache: false,
                data:{
                    branch_model_id: $scope.row.branch_model_id,
                    date: $scope.currmonth,
                    appoint_promotion:$scope.row.appoint_promotion_model_id
                },
                success: function (result) {
                    if (result.code == "1" && result.data) {
                        $scope.ableclassroom = {};
                        angular.forEach(result.data.appoint, function(d){
                            if ($scope.ableclassroom[d['appoint_time']] == undefined) {
                                $scope.ableclassroom[d['appoint_time']] = {};
                            }
                            if ($scope.ableclassroom[d['appoint_time']][d['appoint_course']] == undefined) {
                                $scope.ableclassroom[d['appoint_time']][d['appoint_course']] = {};
                            }
                            $scope.ableclassroom[d['appoint_time']][d['appoint_course']][d['classroom_model_id']] = d;
                        });

                        for(var at in $scope.ableclassroom) {
                            var appoint_times = $scope.ableclassroom[at];
                            var appoint_time_cnt = 0;
                            for(var ac in appoint_times) {
                                var appoint_courses = appoint_times[ac];
                                var appoint_course_cnt = 0;
                                for(var cr in appoint_courses) {
                                    var course = appoint_courses[cr];
                                    var preclassroom = result.data.classroom[course['classroom_model_id']];
                                    if (course['appoint_promotion_model_id'] != $scope.row.appoint_promotion_model_id || (preclassroom['customer_max'] != -1 && course['customer_count'] > preclassroom['customer_max'])) {
                                        course['state'] = false; appoint_course_cnt++;
                                    }
                                }
                                if (appoint_course_cnt >= result.data.classroom.length) {
                                    appoint_courses['state'] = false;appoint_time_cnt++;
                                }
                            }

                            if (appoint_time_cnt >= 4) {
                                appoint_times['state'] = false;
                            }
                        }
                    }
                },
                complete: function (result) {
                    Controller.refreshAppointCourse($scope, month);
                    $('[name="row[period_model_id]"]').selectPageClear();
                }
            });
        },

        evaluate: function () {
            AngularApp.controller("evaluate", function($scope,$sce, $compile,$timeout) {
                $scope.row = row;
                $scope.fields = Config.scenery.fields;
                var html = $compile(Template("view-tmpl", {}))($scope);
                $("#data-view-evaluate").html(html);
            });
        },

        changestaff: function () {
            var self = this;
            AngularApp.controller("changestaff", function($scope,$sce, $compile,$timeout) {
                Controller.addController($scope,$sce, $compile,$timeout).then(function(ret){
                    Backend.api.close(ret);
                });
            });
        },

        changeappointtime: function () {
            var self = this;
            AngularApp.controller("changeappointtime", function($scope,$sce, $compile,$timeout) {
                Form.api.bindevent($("form[role=form]"), function(data, ret){
                    Backend.api.close(ret);
                });
            });
        },
        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.refresh = function(){
                    $.ajax({url: "provider/statistic",dataType: 'json',cache: false,
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
        listevaluate: function () {
            var self = this;
            AngularApp.controller("listevaluate", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.course_model_id = Fast.api.query("course_model_id");
                $scope.searchFieldsParams = function(param) {
                    param.custom = {};
                    param['course_model_id'] = $scope.course_model_id;

                    return param;
                };
                $scope.sceneryInit = function() {
                    var options = {
                        extend: {
                            index_url: 'provider/listevaluate',
                            add_url: '',
                            del_url: '',
                            table: 'provider',
                        },
                        search: false, //是否启用快速搜索
                        commonSearch: false, //是否启用通用搜索

                    };
                    Table.api.init(options);
                    $timeout(function(){$scope.$broadcast("shownTable");},0);


                };

            });
        },

        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});