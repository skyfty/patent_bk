define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        //for index
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },
        indexscape:function($scope, $compile,$timeout){
            $scope.searchFieldsParams = function(param) {
                param.custom = {};
                var branchSelect = $('[name="branch_select"]');
                if (branchSelect.data("selectpicker")) {
                    var branchIds = branchSelect.selectpicker('val');
                    if (branchIds && branchIds.length > 0) {
                        param.custom['branch_model_id'] = ["in", branchIds];
                    }
                }
                return param;
            };
            var options = {
                extend: {
                    index_url: 'customer/index',
                    add_url: 'customer/add',
                    del_url: 'customer/del',
                    multi_url: 'customer/multi',
                    summation_url: 'customer/summation',
                    table: 'customer',
                },
                buttons : [
                    {
                        name: 'view',
                        title: function(row, j){
                            return __(' %s', row.name);
                        },
                        classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                        icon: 'fa fa-folder-o',
                        url: 'customer/view'
                    }
                ]
            };
            Table.api.init(options);
            Form.api.bindevent($("div[ng-controller='index']"));
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "customer/index",dataType: 'json',
                    data:{
                        custom: {"customer.id":$scope.row.id}
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

            $scope.recognition = function() {
                var url = "/customer/recognition?ids=" + $scope.row.id;
                Backend.api.open(url, "识别人脸", {
                    callback: function (res) {
                        $scope.refreshRow();
                    }}
                );
            };
            $scope.syncAvatar = function() {
                require(["jquery-cropper"], function(){

                });
            };

            $("div.mailbox-controls .btn-face-del").on("click", function(){
                var that = this;
                Layer.confirm("确实要删除人脸数据吗？", {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                    function () {
                        var url = Table.api.replaceurl("customer/delface", {ids: $scope.row.id});
                        Fast.api.ajax(url, function () {
                            $scope.refreshRow();
                        });
                        Layer.closeAll();
                    }

                );
            });
        },
        scenery: {
            account:function($scope, $compile,$timeout, data){
                $scope.reckonIds = [];
                $scope.wecont = true;
                $scope.$watch("wecont", function(n,o){
                    if (n!=o) {
                        $scope.$broadcast("refurbish");
                    }
                });

                $scope.chequeChanged = function(data) {
                    var reckonIds = [];
                    angular.forEach(data.selected, function(id){
                        if ($.isNumeric(id))
                            reckonIds.push(id);
                    });
                    $scope.reckonIds = reckonIds;
                    $scope.$broadcast("refurbish");
                };
                $scope.searchFieldsParams = function(param) {
                    param.custom = {
                        "reckon_type":"customer",
                        "reckon_model_id":$scope.row.id,
                    };
                    if ($scope.reckonIds.length > 0) {
                        param.custom['cheque_model_id'] = ["in",$scope.reckonIds];
                    }
                    var types = $("#type").val();
                    if (types) {
                        param.custom['type'] = ["in",types];
                    }

                    if (!$scope.wecont) {
                        param.custom['weshow'] = 1;
                    }
                    return param;
                };

                Table.api.init({
                    extend: {
                        index_url: 'account/index',
                        multi_url: 'account/multi',
                        summation_url: 'account/summation/reckon_type/customer',
                        table: 'account',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'account/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");

                var table = $("#table-account");
                table.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $('a[data-field="weshow"]', table).data("success", function(){
                        $scope.refreshRow();
                    });
                });

                var refresh = function(){
                    $scope.refreshRow();
                };
                $(".btn-add-account").data("callback", refresh);$(".btn-refresh").click(refresh);

                require(['bootstrap-select', 'bootstrap-select-lang'], function () {
                    $('.selectpicker').selectpicker().on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                        $scope.$broadcast("refurbish");
                    }).selectpicker('val', "main");;
                });
            },


            scholarship:function($scope, $compile,$timeout, data){
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));

                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        index_url: 'scholarship/index',
                        del_url: 'scholarship/del',
                        table: 'scholarship',
                    }
                });
                var table = $("#table");

                var tableOptions = {
                    showColumns:true,
                    sortName: 'scholarship',
                    toolbar: "#toolbar-scholarship",
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    columns: [
                        [
                            {field: 'lorerange', title: '类别', align: 'left',
                                formatter: function (value, row, index) {
                                    return value.name.join("-");
                                }
                            },
                            {field: 'knowledge_name', title: '知识', align: 'left'},
                            {field: 'grade_name', title: "等级"},
                            {
                                field: 'scholarship',
                                title: "状态",
                                formatter: function (value, row, index) {
                                    return value?"<span style='color:red'>已获得</span>":"<span style='color:grey'>未获得</span>";
                                }
                            },
                        ]
                    ],
                    queryParams: function (params) {
                        params.custom = {
                            'customer_model_id':$scope.row.id
                        };
                        return params;
                    }
                };
                // 初始化表格
                table.bootstrapTable(tableOptions);
                // 为表格绑定事件
                Table.api.bindevent(table);

                $(".btn-reset").on("click", function(){
                    Layer.confirm("确实要刷新数据吗？", {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function () {
                            var url = Table.api.replaceurl("scholarship/reset", {ids: $scope.row.id});
                            Fast.api.ajax(url, function () {
                                table.bootstrapTable('refresh');
                            });
                            Layer.closeAll();
                        }
                    );
                });
            },

            presell:function($scope, $compile,$timeout, data){
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));

                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        index_url: 'uplift/index',
                        table: 'uplift',
                    }
                });
                var table = $("#table");

                var tableOptions = {
                    toolbar: "#toolbar-uplift",
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    columns: [
                        [
                            {field: 'package.name', title: '课程包', align: 'left'},
                            {field: 'amount', title: "课次"},
                        ]
                    ],
                    queryParams: function (params) {
                        params.custom = {
                            'customer_model_id':$scope.row.id
                        };
                        return params;
                    }
                };
                // 初始化表格
                table.bootstrapTable(tableOptions);
                // 为表格绑定事件
                Table.api.bindevent(table);
            },
            provider:function($scope, $compile,$timeout, data){
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

                $scope.searchFieldsParams = function(params) {
                    params.custom = {
                        'customer_model_id':$scope.row.id
                    };
                    if ($scope.genreModelIds.length > 0) {
                        params.custom['genre_cascader_id'] = ["in",$scope.genreModelIds];
                    }
                    return params;
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

                Table.api.init({
                    extend: {
                        summation_url: 'provider/summation',
                        signin_url:'provider/signin',
                        accomplish_url:'provider/accomplish',
                        table: 'provider',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'provider/hinder'
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
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                var dataTable = $("#table-provider");

                $scope.$broadcast("shownTable");
            },
            business:function($scope, $compile,$timeout, data){
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

                $scope.searchFieldsParams = function(params) {
                    params.custom = {
                        'customer_model_id':$scope.row.id
                    };
                    return params;
                };

                Table.api.init({
                    extend: {
                        summation_url: 'business/summation',
                        add_url: 'business/add',
                        del_url: 'business/del',
                        table: 'business',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'business/hinder'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            wisdom:function($scope, $compile,$timeout, data){
                $scope.searchFieldsParams = function(params) {
                    params.custom = {
                        'customer_model_id':$scope.row.id
                    };
                    return params;
                };

                Table.api.init({
                    extend: {
                        summation_url: 'wisdom/summation',
                        table: 'wisdom',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'wisdom/hinder'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            according: function($scope, $compile,$timeout, data){
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));

                require(['jquery-ui.min', 'fullcalendar', 'fullcalendar-lang'], function () {
                    var events = {
                        url: "customer/schedule",
                        data: function () {
                            return {
                                ids: [$scope.row.id]
                            };
                        }
                    };

                    $('#calendar').fullCalendar({
                        header: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'month,agendaWeek,agendaDay, listMonth'
                        },
                        events: events,
                        navLinks: true,

                        eventAfterAllRender: function (view) {
                            $("a.fc-event[href]").attr("target", "_blank");
                        }
                    });

                });
                $scope.$broadcast("shownTable");
            },
            claim:function($scope, $compile,$timeout, data){
                $scope.searchFieldsParams = function(params) {
                    params.custom = {
                        'customer_model_id':$scope.row.id
                    };
                    return params;
                };

                Table.api.init({
                    extend: {
                        index_url: 'claim/index',
                        del_url: 'claim/del',
                        add_url: 'claim/add?customer_model_id=' + $scope.row.id,
                        table: 'claim',
                    },
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            diploma:function($scope, $compile,$timeout, data){
                $scope.searchFieldsParams = function(params) {
                    params.custom = {
                        'customer_model_id':$scope.row.id
                    };
                    return params;
                };

                Table.api.init({
                    extend: {
                        index_url: 'diploma/index',
                        del_url: 'diploma/del',
                        add_url: 'diploma/add?customer_model_id=' + $scope.row.id,
                        table: 'diploma',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'diploma/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
        },

        bindevent:function($scope){

            $('[name="row[claim_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                    "customer_model_id": $scope.row['id']
                };
                return param;
            });

            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },

        recognition: function () {
            var self = this;
            AngularApp.controller("recognition", function($scope,$sce, $compile,$timeout) {
                $scope.submit = function(data, ret){
                    Fast.api.close(data);
                };
                $scope.face = null;
                $scope.faceimage = null;

                var form = $("form[role=form]");
                Form.events.bindevent(form);
                Form.events.validator(form, $scope.submit, undefined, function(){
                    if ($scope.face && $scope.face.face_token) {
                        $("[name='row[face_token]']").val($scope.face.face_token);
                    }
                    $("[name='row[faceimage]']").val($scope.faceimage);
                });

                $scope.preview = function(data_uri, face) {
                    $scope.face = face;
                    $scope.faceimage = data_uri;
                    $("#preview").attr("src", data_uri);
                    $("#preview-div").addClass('animated tada');
                    setTimeout(function(){$("#preview-div").removeClass('tada');}, 1000);
                };

                $scope.getface = function(data) {
                    if (data && data.error_code == 0 && data.result && data.result.face_num ==1 && data.result.face_list[0].face_probability > 0.7) {
                        return data.result.face_list[0];
                    }
                    return null;
                };

                $scope.capture = function(data_uri) {
                    $.ajax({url:"/aip/detect", type:"POST",data:{image:data_uri,type:"BASE64"}}).then(function(data){
                        var face = $scope.getface(data);
                        if (face) {
                            $scope.preview(data_uri, face);
                            $("#capture").toggleClass('disabled', false);
                        } else {
                            $timeout(function(){Webcam.snap($scope.capture);},1000);
                        }
                    });
                };

                if ($(".plupload", form).size() > 0) {
                    require(['upload'], function(Upload){
                        Upload.api.plupload($(".plupload", form), function(res){
                            var index = Layer.load(0);
                            var options = {
                                url:"/aip/detect",
                                type:"POST",
                                data:{image:res.url,type:"URL"},
                                success: function (data) {
                                    var face = $scope.getface(data);
                                    if (face) {
                                        $scope.preview(res.url, face);
                                    } else {
                                        Fast.events.onAjaxError({code: 0, msg:(data && data.error_msg?data.error_msg: "没有识别出人脸"), data: null});
                                    }
                                },
                                error: function (xhr) {
                                    Fast.events.onAjaxError({code: xhr.status, msg: xhr.statusText, data: null});
                                },
                                complete:function(){
                                    Layer.close(index);
                                }
                            };
                            $.ajax(options);
                        });
                    });
                }

                require(["webcam"], function(Webcam){
                    Webcam.set({
                        width: 320,
                        height: 240,
                        image_format: 'jpeg',
                        jpeg_quality: 90,
                        flip_horiz: true,
                    });
                    Webcam.attach( '#webcam' );

                    $("#capture").click(function(){
                        $scope.face = null;
                        $scope.faceimage = null;
                        $("#preview").attr("src", "/assets/img/customer.png");
                        $timeout(function(){Webcam.snap(function(uri){
                            $scope.capture(uri, "base64");
                        });},1000);
                        $(this).toggleClass('disabled', true);
                    });
                })
            });
        },
        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "customer/statistic",dataType: 'json',cache: false,
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
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});