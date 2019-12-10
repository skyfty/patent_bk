define(['jquery', 'backend', 'table', 'form','template','angular','fast', 'toastr', 'layer', 'upload','moment'], function ($, Backend, Table, Form, Template,Angular, Fast, Toastr, Layer, Upload, moment) {
    window.Angular = Angular;
    window.AngularApp = Angular.module('app', []);

    var Cosmetic = {
        config: {
            defaultsearchfield: {name:"__all_fields__", title:"任意字段",type:"text"}
        },
        api: {
            formatModelKeyword:function(field, data, model) {
                var showData = [];
                if (field.content_list && field.content_list['sight']) {
                    $.each(field.content_list['sight'], function(k, v){
                        if (model[v]) {
                            showData.push(model[v]);
                        }
                    });
                } else {
                    $.each(model, function(k, v){
                        showData.push(v);
                    });
                }

                showData = showData.join(",");

                var control = data[field.name + "_type"] || field.defaultvalue;
                var url = control + "/hinder?ids=" + data[field.name + "_model_id"];
                url = Fast.api.fixurl(url);
                return '<a href="' + url + '" class="dialogit" data-value="' + showData + '" title="' + showData + '">' + showData + '</a>';
            },

            formatter:function(field, data, row) {
                var self = this;
                if (field.type == 'radio' || field.type == 'checkbox' || field.type == 'select' || field.type == 'selects') {
                    if (field.extend == "member" && field.content) {
                        data = row[field.content];
                    } else {
                        var titles = [];
                        data = (data ===null || data === undefined ?[]:  data.toString().split(","));
                        for(var i in data) {
                            titles.push(field.content_list[data[i]]);
                        }
                        data = Table.api.formatter.label.call(this, titles,field);
                    }

                } else if (field.type=="model") {
                    if (field.relevance) {
                        data = data[field.relevance];
                    }
                    var modelKeyword = data[field.name + "_model_keyword"];
                    if (modelKeyword) {
                        var showData = [];
                        modelKeyword = $.parseJSON(modelKeyword);
                        if ($.isArray(modelKeyword)) {
                            $.each(modelKeyword, function(k, v){
                                showData.push(self.formatModelKeyword(field, data, $.parseJSON(v)));
                            });
                        } else {
                            showData.push(self.formatModelKeyword(field, data, modelKeyword));
                        }
                        if (showData.length == 1) {
                            data = showData[0];
                        } else {
                            data = showData.join("<br/>");
                        }

                    } else {
                        data = "-";
                    }
                } else if (field.type=="cascader") {
                    if (field.relevance) {
                        data = data[field.relevance];
                    }
                    var cascaderKeyword = data[field.name + "_cascader_keyword"];
                    if (cascaderKeyword) {
                        cascaderKeyword = JSON.parse(cascaderKeyword);
                        data = cascaderKeyword['name'].join("/");
                    } else {
                        data = "-";
                    }
                } else {
                    if (Table.api.formatter[field.type]) {
                        data = Table.api.formatter[field.type].call(this, data, row);

                    }
                }
                return data;
            },
            getHtml:function(scope, url, success, error) {
                var index = Layer.load();
                var options = {url:url, type: "GET", success: success,  error: error,
                    beforeSend:function(){
                    },
                    complete:function(){
                        Layer.close(index);
                    }
                };
                $.ajax(options);
            },

            showFieldTip:function(field, text, hi) {
                if (angular.isString(field)) {
                    field = $('div[modelfield="'+field+'"]');
                }
                field.qtip({
                    style: {
                        classes: 'qtip-bootstrap',
                    },
                    show: true,
                    position: {
                        my: 'bottom left',
                        at: 'top right'
                    },

                    hide: {
                        event: (hi?hi:false)
                    },
                    content: {
                        text: text
                    }
                }).qtip('show');
            },
            errorFlash:function(el) {
                $(el).addClass('animated flash');
                setTimeout(function(){
                    $(el).removeClass('flash');
                }, 1000);
            }
        },

        bindevent:function($scope){
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
            Form.api.bindevent($("form[role=form]"), $scope.submit);
        },

        indexscape:function($scope, $compile,$timeout){
        },

        index: function () {
            var self = this;
            AngularApp.controller("index", function($scope, $compile,$timeout) {
                $scope.sceneryInit = function() {
                    self.indexscape($scope, $compile,$timeout);
                };
                $scope.getHtml = function(url, success, error){
                    Cosmetic.api.getHtml($scope, url, success, error);
                };
                $scope.refurbishSearch = function(idx, ss){
                    angular.element('#tab-'+idx).scope().$broadcast('refurbish', ss);
                };

                $scope.cleanSearch = function(idx){
                    angular.element('#tab-'+idx).scope().$broadcast('cleanSearch');
                };

                $scope.scenerys = {};
                angular.forEach(Config.sceneryList['index'], function(scenery){
                    $scope.scenerys[scenery.name] =  $.extend({}, scenery, {
                        "group":false,
                        "groupName":"",
                        "complex":false
                    });
                });
                $scope.allFields = Config.allFields;

                //绑定事件
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    var panel = $($(this).attr("href"));
                    if (panel.size() <= 0) {
                        return;
                    }

                    var idx = $(this).attr("data-scenery-index");
                    var scenery = $scope.scenerys[idx];
                    switch(scenery.type) {
                        case "url": {
                            var url = Config.controllername + "/" + scenery.name + "/action/" + Config.actionname ;
                            if (scenery['extend'] != '') {
                                url += "/" + scenery['extend'];
                            }
                            $scope.getHtml(Fast.api.fixurl(url), function(data){
                                angular.element("#tab-" +idx).scope().$broadcast('shownTab', data);
                            });
                            break;
                        }
                        default: {
                            angular.element("#tab-" +idx).scope().$broadcast('shownTab', scenery);
                            break;
                        }
                    }

                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                    $(this).unbind('shown.bs.tab');
                });

                $timeout(function(){$('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");},0);
            });

            angular.forEach(Config.sceneryList['index'], function(scenery){
                AngularApp.controller("landscape-" + scenery.name, function($scope, $compile,$timeout) {
                    $scope.sceneryInit = function(idx) {
                        $scope.scenery = $scope.scenerys[idx];
                        $scope.fields = $scope.scenery.fields;

                        $scope.$on('shownTab', function(event,data) {
                            if (typeof self['lands'][$scope.scenery.name] == "function") {
                                self['lands'][$scope.scenery.name]($scope, $compile,$timeout, data);
                            }
                            $scope.$broadcast("shownTable");
                        });
                    };
                });
            });
            $('#landscape-tab a:first').tab('show');
        },

        sceneryscape:function() {

        },

        viewscape:function() {

        },

        add: function () {
            var self = this;
            AngularApp.controller("add", function($scope,$sce, $compile,$timeout) {
                $scope.fields = Config.scenery.fields;
                $scope.row = {};
                $scope.row['creator_model_id'] = $scope.row['owners_model_id'] = Config.admin_id;
                var branch_model_id = Backend.api.query("branch_model_id");
                if (branch_model_id) {
                    $scope.row['branch_model_id'] = branch_model_id;
                } else{
                    $scope.row['branch_model_id'] = Config.admin_branch_model_id!= null?Config.admin_branch_model_id:0;
                }
                $scope.submit = function(data, ret){
                    Backend.api.close(data);
                };
                var html = Template("edit-tmpl",{state:"add",'fields':"fields"});
                $timeout(function(){
                    $("#data-view").html($compile(html)($scope));
                    $timeout(function(){
                        self.bindevent($scope);
                    });
                });
            });
        },

        view: function () {
            var self = this;
            var index = Layer.load();
            AngularApp.controller("view", function($scope, $parse, $compile,$timeout) {

                $scope.sceneryInit = function() {
                    // 给上传按钮添加上传成功事件
                    $("#plupload-avatar").data("upload-success", function (data) {
                        var url = Backend.api.cdnurl(data.url);
                        $(".profile-user-img").prop("src", url);
                        $("#update-form-myavatar").submit();
                    });
                    Form.api.bindevent($("#update-form-myavatar"));

                    self.viewscape($scope, $compile,$parse, $timeout);
                };

                $scope.getHtml = function(url, success, error){
                    Cosmetic.api.getHtml($scope, url, success, error);
                };

                var block = Config.sceneryList['block'];
                if (block) {
                    $scope.block = block[0];
                }

                $scope.scenerys = {};
                angular.forEach(Config.sceneryList['view'], function(scenery){
                    $scope.scenerys[scenery.name] =  $.extend({}, scenery, {});
                });

                $scope.row = row;
                $scope.viewstate = {
                    "editing":false,
                };
                $scope.navtitle = "";

                $("div.mailbox-controls .btn-del").on("click", function(){
                    var that = this;
                    Layer.confirm(__('Are you sure you want to delete this item?'), {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
                        function () {
                            var url = $(that).attr("href");
                            url = Table.api.replaceurl(url, {ids: $scope.row.id});
                            Fast.api.ajax(url, function () {
                                Fast.api.close();
                                Fast.api.closetabs();
                            });
                            Layer.closeAll();
                        }
                    );
                });

                $scope.redrawScenery = function(idx){
                    var scenery = $scope.scenerys[idx];
                    switch(scenery.type) {
                        case "url": {
                            var url = Table.api.replaceurl( Config.controllername + "/" + scenery.name, {ids: $scope.row.id});
                            if (scenery['extend'] != '') {
                                url += "/" + scenery['extend'];
                            }
                            $scope.getHtml(Fast.api.fixurl(url), function(data){
                                angular.element("#tab-" +idx).scope().$broadcast('shownTab', data);
                            });
                            break;
                        }
                        default: {
                            var tabscope = angular.element("#tab-" +idx).scope();
                            $("#data-view-" + idx).html($compile(Template("view-tmpl", {}))(tabscope));
                            tabscope.$broadcast('shownTab');
                            break;
                        }
                    }
                };

                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    if ($scope.viewstate.editing) {
                        e.preventDefault();
                    }
                    $scope.navtitle = e.target.innerText;

                    var panel = $($(this).attr("href"));
                    if (panel.size() > 0) {
                        var idx = $(this).attr("data-scenery-index");
                        $timeout(function(){$scope.redrawScenery(idx)});
                    }
                    Layer.close(index);
                    $(this).unbind('shown.bs.tab');
                });
                $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
                    if ($scope.viewstate.editing) {
                        e.preventDefault();
                    }
                    $scope.$apply(function(){
                        $scope.navtitle = e.target.innerText;
                    });
                });
                $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
            });

            var initDefaultScenery = function(idx, $scope,$compile,$parse, $timeout){
                $scope.edit =function() {
                    $scope.row = Angular.copy($scope.row);
                    $scope.viewstate.editing = true;
                    $("#data-edit-" + idx).append($compile(Template("edit-tmpl",{state:"edit",'fields':"fields"}))($scope));
                    $timeout(function(){
                        var roleForm = $("form[role=form]");
                        var validator = roleForm.data("validator");
                        if (validator) {
                            validator.reset();
                        }
                        self.bindevent($scope,$timeout);
                    });
                };

                $scope.submit = function(data, ret) {
                    $scope.$apply(function(){
                        $scope.repeal();
                        $parse("row").assign($scope.$parent, data);
                    });
                };

                $scope.repeal = function(){
                    $scope.viewstate.editing = false;
                    delete $scope.row;
                    $("#data-edit-" + idx).html("");
                };
            };


            angular.forEach(Config.sceneryList['view'], function(scenery){
                var contrfunc = function($scope, $parse, $compile,$timeout){
                    $scope.sceneryInit = function(idx) {
                        $scope.scenery = $scope.scenerys[idx];
                        $scope.fields = $scope.scenery.fields;

                        switch ($scope.scenery.type ) {
                            case "url": {
                                break;
                            }
                            default: {
                                initDefaultScenery(idx, $scope, $compile, $parse, $timeout);
                                break;
                            }
                        }
                        $scope.$on('shownTab', function(event,data) {
                            if (self['scenery'] && typeof self['scenery'][$scope.scenery.name] == "function") {
                                self['scenery'][$scope.scenery.name]($scope, $compile,$timeout, data);
                            }
                        });
                    }
                };
                AngularApp.controller("scenery-" + scenery.name,  contrfunc);
            });

            $('#scenery-tab a:first').tab('show');
        },

        hinder: function () {
            var self = this;
            var index = Layer.load();
            AngularApp.controller("hinder", function($scope, $compile,$parse, $timeout) {
                $scope.row = row;
                $scope.sceneryInit = function(){
                    self.viewscape($scope, $compile,$parse, $timeout);
                };

                $scope.scenerys = {};
                angular.forEach(Config.sceneryList['hinder'], function(scenery){
                    $scope.scenerys[scenery.name] =  $.extend({}, scenery, {});
                });

                $scope.redrawScenery = function(idx){
                    var scenery = $scope.scenerys[idx];
                    switch(scenery.type) {
                        case "url": {
                            var url = Table.api.replaceurl(Config.controllername + "/" + scenery.name, {ids: $scope.row.id});
                            $scope.getHtml(Fast.api.fixurl(url), function(data){
                                angular.element("#tab-" +idx).scope().$broadcast('shownTab', data);
                            });
                            break;
                        }
                        default: {
                            var tabscope = angular.element("#tab-" +idx).scope();
                            $("#data-view-" + idx).html($compile(Template("view-tmpl", {}))(tabscope));
                            tabscope.$broadcast('shownTab');
                            break;
                        }
                    }
                };

                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    var panel = $($(this).attr("href"));
                    if (panel.size() > 0) {
                        var idx = $(this).attr("data-scenery-index");
                        $timeout(function(){$scope.redrawScenery(idx)});
                    }
                    Layer.close(index);
                    $(this).unbind('shown.bs.tab');
                });
                $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
            });

            angular.forEach(Config.sceneryList['hinder'], function(scenery){
                var contrfunc = function($scope){
                    $scope.sceneryInit = function(idx) {
                        $scope.scenery = $scope.scenerys[idx];
                        $scope.fields = $scope.scenery.fields;
                    }
                };
                AngularApp.controller("scenery-" + scenery.name,  contrfunc);
            });

            $('#scenery-tab a:first').tab('show');
        },

        logs:function() {
        },
        defaultAction:function(){
        }
    };

    window.AngularApp.directive("magicfield", function() {
        return {
            controller: function($scope,$element,$attrs, $filter, $compile,$parse){
                var field = $parse($attrs.field)($scope);
                var data = $parse($attrs.model)($scope);
                var html = "";
                if (Form.formatter[field.type]) {
                    if (field.type == "model" || field.type == "cascader") {
                        html = Form.formatter[field.type]($attrs.scene,field, data);
                    }else if (field.name == "idcode"){
                        html =  Form.formatter.idcode($attrs.scene,field, data[field.name]);
                    }else {
                        html =  Form.formatter[field.type]($attrs.scene,field, data[field.name], data);
                    }
                }
                $element.html($compile(html)($scope));
            }
        };
    });

    window.AngularApp.directive("selectpage", function($parse) {
        return {
            link: function($scope, $element, $attrs) {
                $element.data("e-selected", function(data){
                    $scope.$apply(function(){
                        $parse($attrs.model).assign($scope.$parent, data.id);
                    });
                });
                $element.data("e-clear", function(data){
                    $scope.$apply(function(){
                        $parse($attrs.model).assign($scope.$parent,null);
                    });
                });
            }
        };
    });

    window.AngularApp.directive("sortable", function($parse) {
        return {
            restrict : 'A',
            link: function($scope, $element, $attrs) {
                require(['sortable'], function (Sortable) {
                    var options = {
                        animation: 150,
                        dataIdAttr: 'data-id',
                    };
                    if ($attrs.group) {
                        options['group'] = $attrs.group
                    }
                    if ($attrs.filter) {
                        options['filter'] = $attrs.filter
                    }
                    var sortable = Sortable.create($element[0], options);
                    $element.data('sortable', sortable);
                });
            }
        };
    });

    window.AngularApp.directive("boostrapSwitcher", function($parse) {
        return {
            restrict : 'C',
            link: function($scope, $element, $attrs) {
                require(['bootstrap-switch'], function () {
                    $($element).bootstrapSwitch({
                        onText:$attrs.onLabel?$attrs.onLabel:"开",
                        offText:$attrs.offLabel?$attrs.offLabel:"关",
                        onColor:"info",
                        offColor:"success",
                        size:"mini",
                        onSwitchChange:function(event,state){
                            if(state==true){
                                $(this).val("1");
                            }else{
                                $(this).val("0");
                            }
                            $scope.$apply(function(){
                                $parse($attrs.model).assign($scope,state);
                            })
                        }
                    }).bootstrapSwitch('state',true);;
                });

            }
        };
    });

    window.AngularApp.directive("datetimepicker", function($parse) {
        return {
            restrict : 'C',
            link: function($scope, $element, $attrs) {
                if ($attrs.model) {
                    $($element).on('dp.change', function(ev){
                        if (ev.date != ev.oldDate) {
                            var val = $element.val();
                            $scope.$apply(function(){
                                $parse($attrs.model).assign($scope, val);
                            });
                        }
                    });
                }
            }
        };
    });

    window.AngularApp.directive("selectpicker", function($parse) {
        return {
            restrict : 'C',
            link: function($scope, $element, $attrs) {
                if ($attrs.model) {
                    $element.on('changed.bs.select', function (e) {
                        var val = $element.val();
                        $scope.$apply(function(){
                            $parse($attrs.model).assign($scope, val);
                        });
                    });
                }
            }
        };
    });


    window.AngularApp.directive("cascader", function($parse) {
        return {
            restrict : 'C',
            link: function($scope, $element, $attrs) {
                var hiddenInput = $("[name='row["+$attrs.fieldName+"_cascader_keyword]']", $element.parent());
                var hiddenIdInput = $("[name='row["+$attrs.fieldName+"_cascader_id]']", $element.parent());
                require(['cascader'], function (cascader) {
                    var val = hiddenInput.val();
                    cascader({
                        elem: $element,
                        url: $attrs.source,
                        value: val?JSON.parse(val)['ids']:"",
                        success: function (valData,labelData) {
                            $scope.$apply(function() {
                                if (valData && valData.length > 0) {
                                    var retsult = JSON.stringify({
                                        ids:valData,
                                        name:labelData
                                    });
                                    if (retsult != val) {
                                        hiddenInput.val(retsult);
                                        $parse($attrs.keywordModel).assign($scope.$parent, retsult);
                                        $parse($attrs.model).assign($scope.$parent, valData[valData.length - 1]);
                                        hiddenIdInput.val(valData[valData.length - 1]);
                                        hiddenIdInput.trigger("change");
                                    }
                                } else {
                                    if (val != "") {
                                        hiddenInput.val("");
                                        $parse($attrs.keywordModel).assign($scope.$parent, "");
                                        $parse($attrs.model).assign($scope.$parent, null);
                                        hiddenIdInput.val("");
                                        hiddenIdInput.trigger("change");
                                    }
                                }
                            });
                        }
                    });
                });
            }
        };
    });


    window.AngularApp.directive("formatter", function($parse, $compile) {
        return {
            link: function($scope, $element, $attrs) {
                var field = $parse($attrs.field)($scope);
                var fieldName  = "row." + field.name;
                if (field.type == "model") {
                    fieldName += "_model_id";
                }else if (field.type == "cascader") {
                    fieldName += "_cascader_id";
                }
                $scope.$watch(fieldName, function(){
                    var row = $parse($attrs.model)($scope);
                    if (field.type == "model" || field.type == "cascader") {
                        var data = row;
                    } else {
                        if (field.relevance != "") {
                            var data = row[field.relevance][field.name];
                        } else {
                            var data = row[field.name];
                        }
                    }
                    var html = Cosmetic.api.formatter(field, data, row);
                    $element.html(html?html:"-");
                });
            }
        };
    });

    window.AngularApp.directive("spotcircus", function() {
        return {
            scope:true,
            replace: true,
            controller: function($scope,$element,$attrs, $filter, $compile,$parse){
                $scope.$watch($attrs.field, function(){
                    var field = $parse($attrs.field)($scope);
                    if (field) {
                        var html = Template("spotcircus-tmpl",{f:field, d:$attrs.fieldModel});
                        var form = $compile(html)($scope);
                        if(field.type=="datetime" || field.type == "date" || field.type=="time") {
                            Form.events.daterangepicker(form);
                        }else if(field.type=="checkbox"||field.type=="radio"||field.type=="select"||field.type=="selects"){
                            Form.events.selectpicker(form);
                        }
                        $element.html(form);
                    }
                });
            }
        };
    });
    window.AngularApp.directive("spotfields", function() {
        return {
            scope:true,
            replace: true,
            controller: function($scope,$element,$attrs, $filter, $compile,$parse, $timeout){
                var fields = $parse($attrs.fields)($scope);
                $scope.fields = fields;
                $scope.onFieldChange = function(ele) {
                    $timeout(function(){
                        var field = $parse($attrs.ngModel + ".field")($scope);
                        $scope.$apply(function(){
                            $parse($attrs.ngModel + ".value").assign($scope, "");
                            var condition = "LIKE %...%";
                            if(field.type=="datetime" || field.type == "date" || field.type=="time") {
                                condition = "between time";
                            }else if(field.type=="checkbox"||field.type=="radio"||field.type=="select"||field.type=="selects"){
                                condition = "IN(...)";
                            }else if(field.type=="number") {
                                condition = "=";
                            }else if(field.type=="model") {
                                condition = "QJSON";
                            }
                            $parse($attrs.ngModel + ".condition").assign($scope, condition);
                        });
                    },0);
                };
                var html = $compile(Template("spotfields-tmpl",{f:fields, d:$attrs.ngModel}))($scope);
                Form.api.bindevent(html);
                $element.html(html)
            }
        };
    });

    window.AngularApp.directive("spotgroup", function() {
        return {
            controller: function($scope,$element,$attrs, $filter, $compile,$parse){
                $scope.appendComplex = function(){
                    var complexs = $parse($attrs.ngModel)($scope);
                    complexs.push({field:Cosmetic.config.defaultsearchfield, condition:"=", value:""});
                };

                $scope.removeComplex = function(item) {
                    var complexs = $parse($attrs.ngModel)($scope);
                    complexs.splice(item,1);
                };

                var param = {
                    model: $attrs.ngModel,
                    sfields:$attrs.fields
                };
                var html = Template('complex-tmpl',param);
                $element.html($compile(html)($scope));
            }
        };
    });

    window.AngularApp.directive("datetimerange", function() {
        return {
            restrict : "A",
            controller: function($scope,$element,$attrs, $filter, $compile,$parse){
                $($element).data('callback', function(start, end){
                    $scope.$apply(function(){
                        $parse($attrs.ngModel).assign($scope, start.format("YYYY/MM/DD") + " - " + end.format("YYYY/MM/DD"));
                    });
                });
                $($element).on('show.daterangepicker', function (ev, picker) {
                    $("div.daterangepicker.dropdown-menu").css("z-index", 29891015);
                });
            }
        };
    });


    window.AngularApp.directive("citypicker", function() {
        return {
            controller: function($scope,$element,$attrs, $filter, $compile,$parse){
                $scope.$watch($attrs.ngModel, function(){
                    if (typeof $element.citypicker != "undefined") {
                        $element.trigger('change');
                    }
                });
            }
        };
    });

    window.AngularApp.directive('stafftree', function() {
        return {
            scope:true,
            controller: function($scope,$element,$attrs, $compile,$parse){
                var cb = $parse($attrs.changed)($scope);
                var custom = $parse($attrs.params)($scope);

                require(['jstree'], function () {
                    $element.html($compile(Template('stafftree-tmpl',{}))($scope));
                    var selected = [];

                    $(".channeltree", $element).jstree({
                        "themes": {
                            "stripes": true
                        },
                        "checkbox": {
                            "keep_selected_style": false,
                        },
                        "types": {
                            "list": {
                                "icon": "fa fa-user-secret",
                            }
                        },
                        'plugins': ["types", "checkbox"],
                        "core": {
                            "multiple": true,
                            'check_callback': true,
                            'data': function (obj, callback) {
                                var content = $(".search-content", $element).val();
                                if (custom) {
                                    if ($.isFunction(custom)) {
                                        custom = custom()
                                    }
                                }
                                var data = {
                                    'search':content
                                };
                                if (custom) {
                                    data['custom'] = custom;
                                }

                                $.ajax({
                                    url: 'staff/index',
                                    dataType: 'json',
                                    data:data,
                                    success: function (data) {
                                        selected = [];
                                        var staffList = [];
                                        $.each(data.rows, function (k, v) {
                                            if (v.admin_id) {
                                                staffList.push({
                                                    'id': "tree_" + v['id'],
                                                    'parent': '#',
                                                    'text': v.name + "," + v.idcode,
                                                    'type': "list",
                                                    'data': v,
                                                });
                                            }
                                        });
                                        callback(staffList);
                                        if (cb) {
                                            cb(selected)
                                        }
                                    }
                                });
                            }
                        }
                    });

                    //全选和展开
                    $(".checkall", $element).on("click", function () {
                        $(".channeltree", $element).jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                    });

                    $('.channeltree', $element).on("changed.jstree", function (e, data) {
                        selected = $(".channeltree", $element).jstree().get_checked(true);
                        if (cb) {
                            cb(selected)
                        }
                        return false;
                    });

                    $(".search-btn", $element).click(function () {
                        $('.channeltree', $element).jstree(true).refresh();
                    });
                    $(".search-btn", $element).trigger("click");
                });
            }
        };
    });

    window.AngularApp.filter('availableFields', function () {
        return function (collection, state) {
            var newCollection = [];
            angular.forEach(collection, function(f){
                if ((state=="edit"?f.editstatus:f.newstatus) != "disabled") {
                    newCollection.push(f);
                }
            });
            return newCollection;
        };
    });

    window.AngularApp.filter('visibleField', function () {
        return function (field, state) {
            return (state=="edit"?field.editstatus:field.newstatus) != "hidden";
        };
    });

    window.AngularApp.filter('selectOption', function () {
        return function (field, state) {
            return (state=="edit"?field.editstatus:field.newstatus) != "hidden";
        };
    });

    window.AngularApp.filter('moneyFmt', function () {
        return function (val) {
            return (val?val:"0.00");
        };
    });

    window.AngularApp.directive('uiButterbar', ['$rootScope', '$anchorScroll', function($rootScope, $anchorScroll) {
        return {
            restrict: 'AC',
            template:'<span class="bar"></span>',
            link: function(scope, el, attrs) {
                el.addClass('butterbar');
                scope.$on('$stateChangeStart', function(event) {
                    $anchorScroll();
                    el.removeClass('hide').addClass('active');
                });
                scope.$on('$stateChangeSuccess', function( event, toState, toParams, fromState ) {
                    event.targetScope.$watch('$viewContentLoaded', function(){
                        el.addClass('hide').removeClass('active');
                    })
                });

                scope.$on('butterbarEvent', function(event, args) {
                    if (args.show == true) {
                        $anchorScroll();
                        el.removeClass('hide').addClass('active');
                    } else {
                        el.addClass('hide').removeClass('active');
                    }
                });
            }
        };
    }]);

    window.AngularApp.directive('validatorObserve',function(){
        return {
            controller: function($scope,$element,$attrs,$parse, $timeout){
                var field = $parse($attrs.validatorObserve)($scope);
                var rate = function(ishide) {
                    if (field.type == "model") {
                        var ele = $('input[name="row['+field.name+'_model_id]"]', $element);
                    }else if (field.type == "cascader"){
                        var ele = $('input[name="row['+field.name+'_cascader_id]"]', $element);
                    } else {
                        var ele = $('.form-field-'+field.name, $element);
                    }
                    if (ishide) {
                        ele.attr("novalidate", "novalidate");
                    } else {
                        ele.removeAttr("novalidate");
                    }
                };
                $element.on('rate', function () {
                    rate($element.is(':hidden'));
                });
                $timeout(function(){$element.trigger("rate");},100);
            }
        }
    });

    window.AngularApp.directive('filterCondition',function(){
        return {
            controller: function($scope,$element,$attrs,$parse, $timeout){
                $element.on('changed.bs.select', function () {
                    var fcope = angular.element("#" + $attrs.filterCondition).scope();
                    var sels = $element.val();
                    $timeout(function(){
                        fcope.$apply(function(){
                            $parse($attrs.conditionModel).assign(fcope, sels);
                        });
                        fcope.$broadcast('refurbish');
                    },100);

                });
                $scope.$on("cleanSearch", function($event, data) {
                    var selec = $element.selectpicker('val');
                    if (selec == null || selec.length == 0) {
                        $timeout(function() {
                            angular.element("#" + $attrs.filterCondition).scope().$broadcast('refurbish');
                        }, 100);
                    } else {
                        $element.selectpicker('deselectAll');
                    }
                });
            }
        }
    });

    window.AngularApp.directive("location", function() {
        return {
            scope:true,
            controller: function($scope,$element,$attrs, $filter, $compile,$parse, $timeout){
                var input =$('<input readonly width="100%" data-responsive="true" class="form-control" name="'+$attrs.name+'"  ng-model="'+$attrs.ngModel+'" type="text" placeholder="详细地址"></div>');
                var iname = "row[" + $attrs.name + "]";
                input.attr("name", iname);
                input = $compile(input.prop("outerHTML"))($scope);

                input.on("click", function(){
                    var data = $parse($attrs.ngModel)($scope);
                    var lat,lng;
                    var locationData = data.split(",");
                    if (locationData && locationData.length == 3) {
                        lat = locationData[1];
                        lng = locationData[2];
                    }
                    var url = "/addons/address/index/select?setpoint=1&";
                    url += (lat && lng) ? 'lat=' + lat + '&lng=' + lng : '';
                    Fast.api.open(url, '位置选择', {
                        callback: function (ret) {
                            if (ret.address && ret.lat && ret.lng) {
                                $scope.$apply(function() {
                                    var address = ret.address +","+ ret.lat + "," + ret.lng;
                                    $parse($attrs.ngModel).assign($scope, address);
                                });
                            }
                        }
                    });
                });
                $element.html(input);
            }
        };
    });


    window.AngularApp.directive('ztree',['$parse', function($parse){
        return {
            restrict : 'A',
            link: function($scope,$element,$attr){
                var clickCallback =  $parse($attr.click)($scope);
                var beforeDragCallback =  $parse($attr.beforeDrag)($scope);
                var beforeDropCallback =  $parse($attr.beforeDrop)($scope);
                var dropCallback =  $parse($attr.drop)($scope);
                var asyncCallback =  $parse($attr.async)($scope);
                var customCallback =  $parse($attr.custom)($scope);
                var addDiyDom =  $parse($attr.addDiyDom)($scope);
                var createdCallback =  $parse($attr.created)($scope);
                var addHoverDom =  $parse($attr.addHoverDom)($scope);
                var removeHoverDom =  $parse($attr.removeHoverDom)($scope);
                var onCheck =  $parse($attr.onCheck)($scope);
                var filter =  $parse($attr.filter)($scope);

                require(['ztree'], function () {
                    var className = "dark", curDragNodes, autoExpandNode;

                    var setting = {
                        edit: {
                            enable: ($attr.editable?($attr.editable == "true"):false),
                            showRemoveBtn: ($attr.showRemove?$attr.showRemove:false),
                            showRenameBtn: ($attr.showRename?$attr.showRename:false),
                            drag:{
                                isCopy: ($attr.editDragCopy?($attr.editDragCopy == "true"):false),
                                isMove: ($attr.editDragMove?($attr.editDragMove == "true"):false),
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
                        async: {
                            enable: true,
                            url:$attr.url,
                            autoParam:[
                                "id", "name=n", "level=lv"
                            ],
                            otherParam:{
                                "embody":$attr.embody,
                                "pid":$attr.pid,
                            },
                            dataFilter: function(treeId, parentNode, childNodes) {
                                if (typeof filter == "function") {
                                    return filter.call($element, treeId, parentNode, childNodes);
                                }
                                return childNodes;
                            }
                        },
                        callback: {
                            onClick: function(event, treeId, treeNode, clickFlag) {
                                if (typeof clickCallback == "function") {
                                    return clickCallback.call($element, event, treeId, treeNode, clickFlag);
                                }
                                return false;
                            },
                            beforeDrag:  function(treeId, treeNodes) {
                                className = (className === "dark" ? "":"dark");
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

                                if (typeof beforeDragCallback == "function") {
                                    return beforeDragCallback.call($element, treeId, treeNodes);
                                }
                                return true;
                            },
                            beforeDrop:  function(treeId, treeNodes, targetNode, moveType) {
                                if (typeof beforeDropCallback == "function") {
                                    return beforeDropCallback.call($element, treeId, treeNodes, targetNode, moveType);
                                }
                                return true;
                            },
                            onDrop:  function(event, treeId, treeNodes, targetNode, moveType) {
                                if (typeof dropCallback == "function") {
                                    return dropCallback.call($element, event, treeId, treeNodes, targetNode,moveType);
                                }
                                return true;
                            },
                            onAsyncSuccess: function(event, treeId, treeNode, msg) {
                                if (typeof asyncCallback == "function") {
                                    return asyncCallback.call($element, event, treeId, treeNode, msg);
                                }
                                return true;
                            },
                            onNodeCreated: function(event, treeId, treeNode) {
                                if (typeof createdCallback == "function") {
                                    return createdCallback.call($element, event, treeId, treeNode);
                                }
                                return true;
                            },
                            onCheck: function(event, treeId, treeNode) {
                                if (typeof onCheck == "function") {
                                    return onCheck.call($element, event, treeId, treeNode);
                                }
                                return true;
                            }
                        }
                    };

                    if (typeof customCallback == "function") {
                        setting['async']['otherParam']['custom'] = customCallback;
                    }
                    setting['view'] = {};
                    if (typeof addDiyDom == "function") {
                        setting['view']['addDiyDom'] = addDiyDom;
                    }
                    if (typeof addHoverDom == "function") {
                        setting['view']['addHoverDom'] = addHoverDom;
                    }
                    if (typeof removeHoverDom == "function") {
                        setting['view']['removeHoverDom'] = removeHoverDom;
                    }

                    setting['check'] = {};
                    if ($attr.check == "check") {
                        setting['check']['enable'] = true;
                        setting['check']['autoCheckTrigger'] = true;

                    }
                    $.fn.zTree.init($($element), setting);
                });
            }
        }
    }]);

    window.AngularApp.directive('btnRequest',function(){
        return {
            link: function($scope,$element,attr){
                var options = $.extend({}, $($element).data() || {});
                if (typeof options.url === 'undefined' && attr.href) {
                    options.url = attr.href;
                }
                options.url = Table.api.replaceurl(options.url, {ids: $scope.row.id});

                var success = typeof $scope[options.success] === 'function' ? $scope[options.success] : null;
                var error = typeof $scope[options.error] === 'function' ? $scope[options.error] : null;
                delete options.success;
                delete options.error;

                $($element).on('click', function (ev, picker) {
                    if (typeof options.confirm !== 'undefined') {
                        Layer.confirm(options.confirm, function (index) {
                            Backend.api.ajax(options, success, error);
                            Layer.close(index);
                        });
                    } else {
                        Backend.api.ajax(options, success, error);
                    }
                    return false;
                });
            }
        }
    });

    window.AngularApp.directive("classifyTree", function() {
        return {
            controller: function($scope,$element,$attrs, $filter, $compile,$parse){
                var self = this;
                var param = {};
                var html = Template('classifytree-tmpl',param);
                $element.html($compile(html)($scope));

                $(".dropdown-menu", $element).on("click", function(event){
                    event.stopPropagation();//阻止了事件的向上传播
                });

                var rendertree = function(data, callback) {
                    $( ".classifytree-checkall",$element).on("click", function () {
                        $(".classifytree",$element).jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                    });
                    $(".classifytree-expandall",$element).on("click", function () {
                        $(".classifytree",$element).jstree($(this).prop("checked") ? "open_all" : "close_all");
                    });
                    $('.classifytree',$element).on("changed.jstree", function (e, data) {
                        if (typeof callback == "function") {
                            callback(data);
                        }
                        return false;
                    });
                    $('.classifytree',$element).jstree({
                        "themes": {
                            "stripes": true
                        },
                        "checkbox": {
                            "keep_selected_style": false,
                        },
                        "types": {
                            "list": {
                                "icon": "fa fa-list",
                            },
                            "link": {
                                "icon": "fa fa-link",
                            },
                            "disabled": {
                                "check_node": false,
                                "uncheck_node": false
                            }
                        },
                        'plugins': ["types", "checkbox"],
                        "core": {
                            "multiple": true,
                            'check_callback': true,
                            "data": data?data:[]
                        }
                    });
                };

                var callback = $parse($attrs.change)($scope);
                if ($attrs.config) {
                    $.ajax({url: $attrs.config,dataType: 'json', success: function (data) {
                            require(['jstree'], function () {
                                rendertree(data, callback);
                            });
                        }
                    });
                }
            }
        };
    });


    var BootstrapTable = $.fn.bootstrapTable.Constructor,
        _initToolbar = BootstrapTable.prototype.initToolbar;
    BootstrapTable.prototype.initToolbar = function () {
        _initToolbar.apply(this, Array.prototype.slice.apply(arguments));

        var initSearchToolbar = this.options.initSearchToolbar;
        if (typeof initSearchToolbar != "undefined") {
            initSearchToolbar.apply(this, Array.prototype.slice.apply(arguments));
        }
    };

    window.AngularApp.directive('uiFormidable', function() {
        return {
            restrict: 'AC',
            scope:true,
            controller: function($scope,$element,$attrs, $filter, $compile,$parse,$timeout){
                var fields = $parse($attrs.fields)($scope);

                var searchFields = $parse($attrs.searchFields)($scope);
                $scope.searchFields = [];
                angular.forEach(searchFields, function(f){
                    if ($.inArray(f.type, ['image','images','file','files']) == -1) {
                        $scope.searchFields.push(f);
                    }
                });
                $scope.searchFields.splice(0, 0, Cosmetic.config.defaultsearchfield);

                var dataTable = $element;
                var formaterColumn = $parse($attrs.formaterColumn)($scope);

                $scope.$on("shownTable", function(){
                    var columns = [
                        {checkbox: true},
                    ];

                    //动态追加字段
                    $.each(fields, function (i, j) {
                        var data = {field: j.name, title: j.title, operate: 'like'};
                        if(j.relevance!=""){
                            data.field = j.relevance + "." + j.name;
                        }
                        data.sortable = true;
                        if (j.type == 'radio' || j.type == 'check' || j.type == 'select' || j.type == 'selects') {
                            data.formatter = function(d, row){
                                return Cosmetic.api.formatter(j, d, row);
                            };
                            data.extend = j.content;
                            var searchList = [];
                            angular.forEach(j.content_list, function(v, k){
                                searchList[v] = k;
                            });
                            data.searchList = searchList;

                        } else if(j.type == "model" || j.type == "cascader"){
                            data.formatter = function(d, row){
                                return Cosmetic.api.formatter(j, row);
                            };
                        } else if(j.type == "address"){
                            data.formatter = function(d, row){
                                return Table.api.formatter.address(row.address,row, i);
                            };
                            data.align = 'left';

                        } else if(j.type == "text" || j.type == "string"){
                            data.align = 'left';
                        } else {
                            if (Table.api.formatter[j.type]) {
                                data.formatter = Table.api.formatter[j.type];
                            }
                            if (j.type == "number") {
                                data.class = "number-total";
                            }
                        }
                        data.custom = j.extend;
                        if (formaterColumn) {
                            data = formaterColumn.apply(dataTable, [j, data]);
                        }
                        columns.push(data);
                    });

                    if ($attrs['nooperate'] != "true") {
                        var operateFormatter = $parse($attrs.operateFormatter)($scope);

                        //追加操作字段
                        columns.push({
                            field: 'operate', title: __('Operate'), table: dataTable,
                            events: Table.api.events.operate,
                            buttons: $.fn.bootstrapTable.defaults.buttons ? $.fn.bootstrapTable.defaults.buttons:[],
                            formatter: (operateFormatter?operateFormatter:Table.api.formatter.operate)
                        });
                    }

                    var queryParams = $parse($attrs.queryParams)($scope);
                    var tableOptions = {
                        url: $attrs.url,
                        commonSearch: false, //是否启用通用搜索
                        toolbar: "#" + $attrs.toolbar,
                        pk: 'id',
                        columns: columns,
                        queryParams: function (params) {
                            params = $scope.tableQueryParams(params);
                            if ($.isFunction(queryParams)) {
                                params = queryParams(params);
                            }
                            params.filter = JSON.stringify(params.filter?params.filter:{});
                            params.op = JSON.stringify(params.op?params.op:{});
                            return params;
                        },
                        initSearchToolbar:function(){
                            var that = this;
                            var buttonParam = {};
                            if ($attrs.searchButton) {
                                buttonParam['customs'] = $attrs.searchButton;
                            } else {
                                buttonParam['customs'] = "";
                            }
                            var buttonHtml = $compile(Template("commonsearchbtn-tmpl", buttonParam))($scope);
                            if ($attrs.searchButtonHideGroup) {
                                $(".dropdown-toggle", buttonHtml).hide();
                                $(".dropdown-menu", buttonHtml).hide();
                            }
                            that.$toolbar.append(buttonHtml);
                        }
                    };
                    if ($attrs.treeShowField) {
                        tableOptions['treeShowField'] = $attrs.treeShowField;
                        tableOptions['parentIdField'] = "pid";
                        tableOptions['idField'] = "id";
                        tableOptions['onLoadSuccess'] = function (data) {
                            dataTable.treegrid({
                                initialState:'collapsed',
                                treeColumn: ($attrs.treeColumn?$attrs.treeColumn:1),
                                onChange: function () {
                                    dataTable.bootstrapTable('resetWidth');
                                }
                            });
                        };
                        tableOptions['sidePagination'] = "server";
                        tableOptions['pagination'] = false;
                        tableOptions['cardView'] = false;
                    }

                    var detailFormatter = $parse($attrs.detailFormatter)($scope);
                    if (detailFormatter) {
                        tableOptions['detailView'] = true;
                        tableOptions['detailFormatter'] = detailFormatter;
                    }

                    dataTable.bootstrapTable(tableOptions);

                    // 为表格1绑定事件
                    Table.api.bindevent(dataTable);


                    //当双击单元格时
                    dataTable.on('dbl-click-row.bs.table', function (e, row, element, field) {
                        $(".btn-view", element).trigger("click");
                    });
                    var options = dataTable.bootstrapTable('getOptions');
                    if (options.extend['summation_url']) {
                        dataTable.bootstrapTable('resetTableTips');
                    }
                });

                $scope.$on("refurbish", function($event, data){
                    data = data?(angular.isArray(data)?data:data.split(",")):[];

                    if ($.inArray("complex",data)!== -1) {
                        $scope.complexSearchCondition = [];
                    }
                    if ($.inArray("group",data)!== -1) {
                        $scope.groupSearchCondition = null;
                    }
                    if ($.inArray("common",data)!== -1) {
                        $scope.commonSearch = {field:Cosmetic.config.defaultsearchfield, condition:"LIKE %...%", value:""};
                    }
                    $scope.searchTable();
                });

                $scope.$on("cleanSearch", function($event, data) {
                    $scope.complexSearchCondition = [];
                    $scope.groupSearchCondition = null;
                    $scope.commonSearch = {field:Cosmetic.config.defaultsearchfield, condition:"LIKE %...%", value:""};
                });

                $scope.tableQueryParams = function(params) {
                    var self = this;
                    var complexSearchCondition = angular.copy($scope.complexSearchCondition);

                    if ($scope.groupSearchCondition != null) {
                        var content = $scope.groupSearchCondition.content?$scope.groupSearchCondition.content:[];
                        if ($scope.groupSearchCondition.type == "cond") {
                            $.each(content, function(k,v){
                                v.field = $scope.allFields[v.field];
                                complexSearchCondition.push(v);
                            });
                        } else {
                            var ids = content.join(",");
                            complexSearchCondition.push({field:{"name":"id"},value:ids,condition:"in"});
                        }
                    }

                    for(var i in $scope.filterCondition) {
                        var ff = $.parseJSON($scope.filterCondition[i]);
                        complexSearchCondition.push({field:{"name":ff[0]},value:ff[2],condition:ff[1]});
                    }

                    if ($scope.commonSearch && $scope.commonSearch.field != null) {
                        if ($scope.commonSearch.value !== null && $scope.commonSearch.value !== "" && typeof $scope.commonSearch.value!=='undefined') {
                            if (angular.isArray($scope.commonSearch.value)) {
                                if ($scope.commonSearch.value.length > 0) {
                                    complexSearchCondition.push($scope.commonSearch);
                                }
                            } else {
                                complexSearchCondition.push($scope.commonSearch);
                            }
                        }
                    }
                    if (params.search) {
                        params.searchField = $scope.complexAllFields($scope.fields);
                    }

                    var filter = {}, op = {};
                    angular.forEach(complexSearchCondition, function (co) {
                        if (co.field !== null && co.value !== "") {
                            var fieldName = co.field.name;
                            if (fieldName === "__all_fields__") {
                                fieldName = $scope.complexAllFields($scope.fields);;
                            }
                            if (co.field.type === "model") {
                                fieldName = fieldName + "_model_keyword";
                            }
                            if (co.field.type === "cascader") {
                                fieldName = fieldName + "_cascader_keyword";
                            }
                            if (co.field.relevance) {
                                fieldName = co.field.relevance+"."+fieldName;
                            }
                            filter[fieldName] = co.value;
                            op[fieldName] = co.condition;
                        }
                    });
                    params.fs = JSON.stringify($scope.filterSearch);
                    params.filter = $.extend({},params.filter?JSON.parse(params.filter):{},filter);
                    params.op = $.extend({},params.op?JSON.parse(params.op):{}, op);
                    if (params.sort) {
                        $.each(fields, function (i, j) {
                            if(j.name == params.sort){
                                if (j.type=="model") {
                                    params.sort = params.sort + "_model_id";
                                }
                                if (j.type=="cascader") {
                                    params.sort = params.sort + "_cascader_id";
                                }
                                if (j.relevance != "") {
                                    params.sort = j.relevance+"."+params.sort;
                                }
                                return false;
                            }
                        });
                    }
                    return params;
                };

                $scope.searchTable = function(){
                    dataTable.bootstrapTable('resetTableTips');
                    var options = dataTable.bootstrapTable('getOptions');
                    options.pageNumber = 1;
                    dataTable.bootstrapTable('refresh', {});
                };

                $scope.deselectAll = function() {
                    $timeout(function(){
                        $('select[ng-model="commonSearch.value"].selectpicker').selectpicker('deselectAll');
                        $scope.searchTable();
                    },100);
                };

                $scope.complexSearchCondition = [];
                $scope.complicateSearch = function(){
                    Layer.open({ type: 1, area: ['70%', '70%'], content: '<div class="complex-form"></div>', btn: [__('OK')],
                        yes: function (index, layero) {
                            Layer.closeAll();
                            $scope.searchTable();
                        },
                        success: function (layero, index) {
                            $(".complex-form", layero).append($compile("<spotgroup ng-model='complexSearchCondition' data-fields='searchFields'/>")($scope));
                        }
                    });
                };
                $scope.$watch("complexSearchCondition", function(){
                    if ($scope.scenery && $scope.scenery.tabstate) {
                        $scope.scenery.tabstate.complex = $scope.complexSearchCondition.length > 0;
                    }
                }, true);

                $scope.groupSearchCondition = null;
                $scope.groupSearch = function(){
                    Fast.api.open("group/select?model_type="+$scope.scenery.model_table+"&multiple=false", __('Choose'), {
                        callback: function (data) {
                            $scope.$apply(function(){
                                $scope.groupSearchCondition = data.gs;
                                dataTable.bootstrapTable('refresh', {});
                            });
                        }
                    });
                };
                $scope.$watch("groupSearchCondition", function(){
                    if ($scope.scenery && $scope.scenery.tabstate) {
                        $scope.scenery.tabstate.group = $scope.groupSearchCondition != null;
                        if ($scope.scenery.tabstate.group) {
                            $scope.scenery.tabstate.groupName = $scope.groupSearchCondition.title;
                        } else {
                            $scope.scenery.tabstate.groupName = "";
                        }
                    }
                });

                $scope.filterCondition = [];
                $scope.commonSearch = {field:Cosmetic.config.defaultsearchfield, condition:"LIKE %...%", value:""};

                $scope.complexAllFields = function(fields) {
                    var fieldArr = [];
                    angular.forEach(fields, function(f){
                        var fieldName = f.name;
                        if (f.relevance) {
                            fieldName = f.relevance+"."+fieldName;
                        }
                        if (f.type == "model") {
                            fieldArr.push(fieldName + "_model_keyword")
                        } else if ($.inArray(f.type, ["text", "string"]) != -1) {
                            fieldArr.push(fieldName)
                        }
                    });
                    fieldArr.push("slug");
                    return fieldArr.join("|");
                }
            },
        };
    });


    window.AngularApp.directive("uiEcharts", function() {
        return {
            controller: function($scope,$element,$attrs, $compile,$timeout) {
                var html = Template('echarts-tmpl', {});
                $element.html($compile(html)($scope));
                require(['echarts','echarts-theme'], function (Echarts,undefined) {
                    var echartdiv = $(".echarts", $element);
                    var myChart = Echarts.init(echartdiv[0], 'walden');
                    var magicType = ($attrs.type == "line"?['line', 'bar']:['pie', 'funnel']);

                    // 指定图表的配置项和数据
                    var option = {
                        title: {
                            text: $attrs.title?$attrs.title:"",
                            subtext: ''
                        },
                        toolbox: {
                            show: true,
                            feature: {
                                mark : {show: true},
                                magicType : {
                                    show: true,
                                    type: magicType,
                                    option: {
                                        funnel: {
                                            x: '25%',
                                            width: '50%',
                                            funnelAlign: 'left',
                                            max: 1548
                                        }
                                    }
                                },
                                restore : {show: true},
                                saveAsImage: {show: true}
                            }
                        },

                        legend: {
                            data:[]
                        },
                        calculable : true,
                        xAxis : [
                            {
                                type : 'category',
                                boundaryGap : false,
                                data : []
                            }
                        ],
                        yAxis : [
                            {
                                type : 'value',
                                axisLabel : {
                                    formatter: '{value}'
                                }
                            }
                        ],
                        grid: [{
                            left: 'left',
                            top: 'top',
                            right: '10',
                            bottom: 30
                        }]
                    };
                    if ($attrs.type == "line") {
                        option['tooltip'] = {
                            trigger: 'axis',
                        };
                    } else {
                        option['tooltip'] = {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        };
                    }
                    myChart.setOption(option);

                    $(window).resize(function () {
                        myChart.resize();
                    });

                    Form.api.bindevent($("form[role=form]", $element));

                    var datetimerange = $(".datetimerange", $element);
                    datetimerange.on("blur", function () {
                        var date = $(this).val();
                        Fast.api.ajax({
                            url: $attrs.url, type: 'GET',
                            data: {
                                scope: date
                            }
                        }, function (data) {
                            myChart.setOption(data);
                            return false;
                        });
                    });
                    $(".btn-refresh", $element).on("click", function () {
                        datetimerange.trigger("blur");
                        $scope.$broadcast("refurbish");
                    });
                    $timeout(function(){
                        var obj = $(".datetimerange", $element).data("daterangepicker");
                        var dates = obj.ranges["本月"];
                        obj.startDate = dates[0];
                        obj.endDate = dates[1];
                        obj.clickApply();
                    },1000);
                });
            }
        };
    });


    window.AngularApp.directive('uiCalendar', function() {
        return {
            restrict: 'AC',
            scope: true,
            controller: function ($scope, $element, $attrs, $filter, $compile, $parse) {
                require(['bootstrap-table'], function () {
                    // 初始化表格
                    $element.bootstrapTable({});

                });

            }
        }
    });
    return Cosmetic;
});