define(['jquery', 'bootstrap', 'upload', 'validator', 'moment'], function ($, undefined, Upload, Validator, moment) {
    var Form = {
        config: {
            fieldlisttpl: '<dd class="form-inline"><input type="text" name="<%=name%>[<%=index%>][key]" class="form-control" value="<%=row.key%>" size="10" /> <input type="text" name="<%=name%>[<%=index%>][value]" class="form-control" value="<%=row.value%>" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span></dd>'
        },
        events: {
            validator: function (form, success, error, submit) {
                if (!form.is("form"))
                    return;
                //绑定表单事件
                form.validator($.extend({
                    validClass: 'has-success',
                    invalidClass: 'has-error',
                    bindClassTo: '.form-group',
                    formClass: 'n-default n-bootstrap',
                    msgClass: 'n-right',
                    stopOnError: true,
                    display: function (elem) {
                        return $(elem).closest('.form-group').find(".control-label").text().replace(/\:/, '');
                    },
                    dataFilter: function (data) {
                        if (data.code === 1) {
                            return data.msg ? { "ok": data.msg } : '';
                        } else {
                            return data.msg;
                        }
                    },
                    target: function (input) {
                        var target = $(input).data("target");
                        if (target && $(target).size() > 0) {
                            return $(target);
                        }
                        var $formitem = $(input).closest('.form-group'),
                            $msgbox = $formitem.find('span.msg-box');
                        if (!$msgbox.length) {
                            return [];
                        }
                        return $msgbox;
                    },
                    valid: function (ret) {
                        var that = this, submitBtn = $(".layer-footer [type=submit]", form);
                        that.holdSubmit(true);
                        submitBtn.addClass("disabled");
                        //验证通过提交表单
                        var submitResult = Form.api.submit($(ret), function (data, ret) {
                            that.holdSubmit(false);
                            submitBtn.removeClass("disabled");
                            if (false === $(this).triggerHandler("success.form", [data, ret])) {
                                return false;
                            }
                            if (typeof success === 'function') {
                                if (false === success.call($(this), data, ret)) {
                                    return false;
                                }
                            }
                            //提示及关闭当前窗口
                            var msg = ret.hasOwnProperty("msg") && ret.msg !== "" ? ret.msg : __('Operation completed');
                            parent.Toastr.success(msg);
                            parent.$(".btn-refresh").trigger("click");
                            var index = parent.Layer.getFrameIndex(window.name);
                            parent.Layer.close(index);
                            return false;
                        }, function (data, ret) {
                            that.holdSubmit(false);
                            if (false === $(this).triggerHandler("error.form", [data, ret])) {
                                return false;
                            }
                            submitBtn.removeClass("disabled");
                            if (typeof error === 'function') {
                                if (false === error.call($(this), data, ret)) {
                                    return false;
                                }
                            }
                        }, submit);
                        //如果提交失败则释放锁定
                        if (!submitResult) {
                            that.holdSubmit(false);
                            submitBtn.removeClass("disabled");
                        }
                        return false;
                    }
                }, form.data("validator-options") || {}));

                //移除提交按钮的disabled类
                $(" [type=submit], .layer-footer [type=submit],.fixed-footer [type=submit],.normal-footer [type=submit]", form).removeClass("disabled");
            },
            selectpicker: function (form) {
                //绑定select元素事件
                if ($(".selectpicker", form).size() > 0) {
                    require(['bootstrap-select', 'bootstrap-select-lang'], function () {
                        $('.selectpicker', form).selectpicker();
                        $(form).on("reset", function () {
                            setTimeout(function () {
                                $('.selectpicker').selectpicker('refresh').trigger("change");
                            }, 1);
                        });
                    });
                }
            },

            colorpicker: function (form) {
                if ($(".colorpicker", form).size() > 0) {
                    require(['colorpicker'], function () {
                       // $('.colorpicker', form).colorpicker();
                    });
                }
            },

            selectpage: function (form) {
                //绑定selectpage元素事件
                if ($(".selectpage", form).size() > 0) {
                    require(['selectpage'], function () {
                        $(".selectpage", form).each(function () {
                            var self = this;
                            var autoSelectFirst = $(this).data("auto-select-first");
                            autoSelectFirst = autoSelectFirst ? autoSelectFirst : false;

                            var options = {
                                eAjaxSuccess: function (data) {
                                    data.list = typeof data.rows !== 'undefined' ? data.rows : (typeof data.list !== 'undefined' ? data.list : []);
                                    data.totalRow = typeof data.total !== 'undefined' ? data.total : (typeof data.totalRow !== 'undefined' ? data.totalRow : data.list.length);
                                    return data;
                                },
                                eSelect:function(data) {
                                    var selected = $(self).data("e-selected");
                                    if (selected) {
                                        selected.call(self, data);
                                    }
                                },
                                autoSelectFirst:autoSelectFirst
                            };
                            var formatItem  = $(this).data('format-item');
                            formatItem = typeof formatItem == 'function' ? formatItem:null;
                            if (formatItem) {
                                options.formatItem = formatItem;
                            }

                            var params = $(this).data("e-params");
                            params = params ? params : false;
                            if (params) {
                                options.params = params;
                            }
                            $(this).selectPage(options);
                        });

                        //给隐藏的元素添加上validate验证触发事件
                        $(document).on("change", ".sp_hidden", function () {
                            $(this).trigger("validate");
                        });
                        $(document).on("change", ".sp_input", function () {
                            $(this).closest(".sp_container").find(".sp_hidden").trigger("change");
                        });
                    });

                    $(form).on("reset", function () {
                        setTimeout(function () {
                            $('.selectpage', form).selectPageClear();
                        }, 1);
                    });
                }
            },
            cxselect: function (form) {
                //绑定cxselect元素事件
                if ($("[data-toggle='cxselect']", form).size() > 0) {
                    require(['cxselect'], function () {
                        $.cxSelect.defaults.jsonName = 'name';
                        $.cxSelect.defaults.jsonValue = 'value';
                        $.cxSelect.defaults.jsonSpace = 'data';
                        $("[data-toggle='cxselect']", form).cxSelect();
                    });
                }
            },
            jsoneditor: function (form) {
                //绑定cxselect元素事件
                if ($("[data-toggle='jsoneditor']", form).size() > 0) {
                    require(['jsoneditor'], function () {
                        var opt = {
                            change: function(data) { /* called on every change */ },
                            propertyclick: function(path) { /* called when a property is clicked with the JS path to that property */ }
                        };
                        $("[data-toggle='jsoneditor']", form).jsonEditor({}, opt);
                    });
                }
            },
            citypicker: function (form) {
                //绑定城市远程插件
                if ($("[data-toggle='city-picker']", form).size() > 0) {
                    require(['citypicker'], function () {
                        $(form).on("reset", function () {
                            setTimeout(function () {
                                $("[data-toggle='city-picker']").citypicker('refresh');
                            }, 1);
                        });
                    });
                }
            },
            datetimepicker: function (form) {
                //绑定日期时间元素事件
                if ($(".datetimepicker", form).size() > 0) {
                    require(['bootstrap-datetimepicker'], function () {
                        var options = {
                            format: 'YYYY-MM-DD HH:mm:ss',
                            icons: {
                                time: 'fa fa-clock-o',
                                date: 'fa fa-calendar',
                                up: 'fa fa-chevron-up',
                                down: 'fa fa-chevron-down',
                                previous: 'fa fa-chevron-left',
                                next: 'fa fa-chevron-right',
                                today: 'fa fa-history',
                                clear: 'fa fa-trash',
                                close: 'fa fa-remove'
                            },
                            showTodayButton: true,
                            showClose: true
                        };
                        $('.datetimepicker', form).parent().css('position', 'relative');
                        $('.datetimepicker', form).datetimepicker(options);
                    });
                }
            },
            daterangepicker: function (form) {
                //绑定日期时间元素事件
                if ($(".datetimerange", form).size() > 0) {
                    require(['bootstrap-daterangepicker'], function () {
                        var ranges = {};
                        ranges[__('Today')] = [Moment().startOf('day'), Moment().endOf('day')];
                        ranges[__('Yesterday')] = [Moment().subtract(1, 'days').startOf('day'), Moment().subtract(1, 'days').endOf('day')];
                        ranges[__('Last 7 Days')] = [Moment().subtract(6, 'days').startOf('day'), Moment().endOf('day')];
                        ranges[__('Last 30 Days')] = [Moment().subtract(29, 'days').startOf('day'), Moment().endOf('day')];
                        ranges[__('This Month')] = [Moment().startOf('month'), Moment().endOf('month')];
                        ranges[__('Last Month')] = [Moment().subtract(1, 'month').startOf('month'), Moment().subtract(1, 'month').endOf('month')];
                        var options = {
                            timePicker: false,
                            autoUpdateInput: false,
                            timePickerSeconds: true,
                            timePicker24Hour: true,
                            autoApply: true,
                            locale: {
                                format: 'YYYY-MM-DD HH:mm:ss',
                                customRangeLabel: __("Custom Range"),
                                applyLabel: __("Apply"),
                                cancelLabel: __("Clear"),
                            },
                            ranges: ranges,
                        };
                        var origincallback = function (start, end) {
                            $(this.element).val(start.format(this.locale.format) + " - " + end.format(this.locale.format));
                            $(this.element).trigger('blur');
                        };
                        $(".datetimerange", form).each(function () {
                            var callback = typeof $(this).data('callback') == 'function' ? $(this).data('callback') : origincallback;
                            $(this).on('apply.daterangepicker', function (ev, picker) {
                                callback.call(picker, picker.startDate, picker.endDate);
                            });
                            $(this).on('cancel.daterangepicker', function (ev, picker) {
                                $(this).val('').trigger('blur');
                            });
                            $(this).daterangepicker($.extend({}, options, $(this).data()), callback);
                        });
                    });
                }
            },
            plupload: function (form) {
                //绑定plupload上传元素事件
                if ($(".plupload", form).size() > 0) {
                    Upload.api.plupload($(".plupload", form));
                }
            },
            faselect: function (form) {
                //绑定fachoose选择附件事件
                if ($(".fachoose", form).size() > 0) {
                    $(".fachoose", form).on('click', function () {
                        var that = this;
                        var multiple = $(this).data("multiple") ? $(this).data("multiple") : false;
                        var mimetype = $(this).data("mimetype") ? $(this).data("mimetype") : '';
                        var admin_id = $(this).data("admin-id") ? $(this).data("admin-id") : '';
                        var user_id = $(this).data("user-id") ? $(this).data("user-id") : '';
                        parent.Fast.api.open("general/attachment/select?element_id=" + $(this).attr("id") + "&multiple=" + multiple + "&mimetype=" + mimetype + "&admin_id=" + admin_id + "&user_id=" + user_id, __('Choose'), {
                            callback: function (data) {
                                var button = $("#" + $(that).attr("id"));
                                var maxcount = $(button).data("maxcount");
                                var input_id = $(button).data("input-id") ? $(button).data("input-id") : "";
                                maxcount = typeof maxcount !== "undefined" ? maxcount : 0;
                                if (input_id && data.multiple) {
                                    var urlArr = [];
                                    var inputObj = $("#" + input_id);
                                    var value = $.trim(inputObj.val());
                                    if (value !== "") {
                                        urlArr.push(inputObj.val());
                                    }
                                    urlArr.push(data.url)
                                    var result = urlArr.join(",");
                                    if (maxcount > 0) {
                                        var nums = value === '' ? 0 : value.split(/\,/).length;
                                        var files = data.url !== "" ? data.url.split(/\,/) : [];
                                        var remains = maxcount - nums;
                                        if (files.length > remains) {
                                            Toastr.error(__('You can choose up to %d file%s', remains));
                                            return false;
                                        }
                                    }
                                    inputObj.val(result).trigger("change").trigger("validate");
                                } else {
                                    $("#" + input_id).val(data.url).trigger("change").trigger("validate");
                                }
                            }
                        });
                        return false;
                    });
                }
            },
            fieldlist: function (form) {
                //绑定fieldlist
                if ($(".fieldlist", form).size() > 0) {
                    require(['dragsort', 'template'], function (undefined, Template) {
                        //刷新隐藏textarea的值
                        var refresh = function (name) {
                            var data = {};
                            var textarea = $("textarea[name='" + name + "']", form);
                            var container = textarea.closest("dl");
                            var template = container.data("template");
                            $.each($("input,select,textarea", container).serializeArray(), function (i, j) {
                                var reg = /\[(\w+)\]\[(\w+)\]$/g;
                                var match = reg.exec(j.name);
                                if (!match)
                                    return true;
                                match[1] = "x" + parseInt(match[1]);
                                if (typeof data[match[1]] == 'undefined') {
                                    data[match[1]] = {};
                                }
                                data[match[1]][match[2]] = j.value;
                            });
                            var result = template ? [] : {};
                            $.each(data, function (i, j) {
                                if (j) {
                                    if (!template) {
                                        if (j.key != '') {
                                            result[j.key] = j.value;
                                        }
                                    } else {
                                        result.push(j);
                                    }
                                }
                            });
                            textarea.val(JSON.stringify(result));
                        };
                        //监听文本框改变事件
                        $(document).on('change keyup', ".fieldlist input,.fieldlist textarea,.fieldlist select", function () {
                            refresh($(this).closest("dl").data("name"));
                        });
                        //追加控制
                        $(".fieldlist", form).on("click", ".btn-append,.append", function (e, row) {
                            var container = $(this).closest("dl");
                            var index = container.data("index");
                            var name = container.data("name");
                            var template = container.data("template");
                            var data = container.data();
                            index = index ? parseInt(index) : 0;
                            container.data("index", index + 1);
                            var row = row ? row : {};
                            var vars = {index: index, name: name, data: data, row: row};
                            var html = template ? Template(template, vars) : Template.render(Form.config.fieldlisttpl, vars);
                            $(html).insertBefore($(this).closest("dd"));
                            $(this).trigger("fa.event.appendfieldlist", $(this).closest("dd").prev());
                        });
                        //移除控制
                        $(".fieldlist", form).on("click", "dd .btn-remove", function () {
                            var container = $(this).closest("dl");
                            $(this).closest("dd").remove();
                            refresh(container.data("name"));
                        });
                        //拖拽排序
                        $("dl.fieldlist", form).dragsort({
                            itemSelector: 'dd',
                            dragSelector: ".btn-dragsort",
                            dragEnd: function () {
                                refresh($(this).closest("dl").data("name"));
                            },
                            placeHolderTemplate: "<dd></dd>"
                        });
                        //渲染数据
                        $(".fieldlist", form).each(function () {
                            var container = this;
                            var textarea = $("textarea[name='" + $(this).data("name") + "']", form);
                            if (textarea.val() == '') {
                                return true;
                            }
                            var template = $(this).data("template");
                            var json = {};
                            try {
                                json = JSON.parse(textarea.val());
                            } catch (e) {
                            }
                            $.each(json, function (i, j) {
                                $(".btn-append,.append", container).trigger('click', template ? j : {
                                    key: i,
                                    value: j
                                });
                            });
                        });
                    });
                }
            },
            bindevent: function (form) {

            },
            slider: function (form) {
                if ($(".slider", form).size() > 0) {
                    require(['bootstrap-slider'], function () {
                        $('.slider').removeClass('hidden').css('width', function (index, value) {
                            return $(this).parents('.form-control').width();
                        }).slider().on('slide', function (ev) {
                            var data = $(this).data();
                            if (typeof data.unit !== 'undefined') {
                                $(this).parents('.form-control').siblings('.value').text(ev.value + data.unit);
                            }
                        });
                    });
                }
            }
        },
        api: {
            submit: function (form, success, error, submit) {
                if (form.size() === 0) {
                    Toastr.error("表单未初始化完成,无法提交");
                    return false;
                }
                if (typeof submit === 'function') {
                    if (false === submit.call(form, success, error)) {
                        return false;
                    }
                }
                var type = form.attr("method") ? form.attr("method").toUpperCase() : 'GET';
                type = type && (type === 'GET' || type === 'POST') ? type : 'GET';
                url = form.attr("action");
                url = url ? url : location.href;
                //修复当存在多选项元素时提交的BUG
                var params = {};
                var multipleList = $("[name$='[]']", form);
                if (multipleList.size() > 0) {
                    var postFields = form.serializeArray().map(function (obj) {
                        return $(obj).prop("name");
                    });
                    $.each(multipleList, function (i, j) {
                        if (postFields.indexOf($(this).prop("name")) < 0) {
                            params[$(this).prop("name")] = '';
                        }
                    });
                }
                //调用Ajax请求方法
                Fast.api.ajax({
                    type: type,
                    url: url,
                    data: form.serialize() + (Object.keys(params).length > 0 ? '&' + $.param(params) : ''),
                    dataType: 'json',
                    complete: function (xhr) {
                        var token = xhr.getResponseHeader('__token__');
                        if (token) {
                            $("input[name='__token__']", form).val(token);
                        }
                    }
                }, function (data, ret) {
                    $('.form-group', form).removeClass('has-feedback has-success has-error');
                    if (data && typeof data === 'object') {
                        //刷新客户端token
                        if (typeof data.token !== 'undefined') {
                            $("input[name='__token__']", form).val(data.token);
                        }
                        //调用客户端事件
                        if (typeof data.callback !== 'undefined' && typeof data.callback === 'function') {
                            data.callback.call(form, data);
                        }
                    }
                    if (typeof success === 'function') {
                        if (false === success.call(form, data, ret)) {
                            return false;
                        }
                    }
                }, function (data, ret) {
                    if (data && typeof data === 'object' && typeof data.token !== 'undefined') {
                        $("input[name='__token__']", form).val(data.token);
                    }
                    if (typeof error === 'function') {
                        if (false === error.call(form, data, ret)) {
                            return false;
                        }
                    }
                });
                return true;
            },
            bindevent: function (form, success, error, submit) {

                form = typeof form === 'object' ? form : $(form);

                var events = Form.events;

                events.bindevent(form);

                events.validator(form, success, error, submit);

                events.selectpicker(form);

                events.colorpicker(form);

                events.daterangepicker(form);

                events.selectpage(form);

                events.cxselect(form);

                events.citypicker(form);

                events.datetimepicker(form);

                events.plupload(form);

                events.faselect(form);

                events.fieldlist(form);

                events.slider(form);

                events.jsoneditor(form);

            },
            custom: {}
        },

        formatter: {
            commonattr:function(scene,input, type, field, val, arr, data) {
                var iname = "";
                if (field['raw_name']) {
                    iname = field['raw_name'];
                } else {
                    iname = "row[" + field.name + "]";
                    if (arr) {
                        iname += "[]";
                    }
                }
                var attr = {
                    "name":iname,
                    "data-rule":field.rule,
                    "data-rule-model":field.model_table,
                    "data-tip":field.tip
                };
                if (data && typeof data['id'] != 'undefined') {
                    attr['data-rule-model-id'] = data['id'];
                }
                input.attr(attr);

                if (type) {
                    input.attr("type", type);
                }
                if (( scene=="add"?field.newstatus:field.editstatus)=='locked') {
                    input.attr("readonly", "readonly");
                }
                if (( scene=="add"?field.newstatus:field.editstatus)=='hidden') {
                    input.hide();
                }
                input.addClass(" form-control " + " form-field-" + field.name);

                return  input;
            },
            common:function(scene,type, field, val, arr, data) {
                var input = $("<input  autocomplete='off' "+field.extend+"/>");
                if (val) {
                    input.attr("value",val);
                }
                return this.commonattr(scene,input,type, field, val, arr, data);
            },
            commontext:function(scene,field, val, data) {
                var input = $("<textarea "+field.extend+"/>");
                input = this.commonattr(scene,input, undefined, field, val, false, data);
                input.attr("rows", 5);
                input.attr("ng-model", "row." + (field.ng_name?field.ng_name:field.name));
                if (val) {
                    input.attr("value",val);
                }
                return input;
            },
            text: function (scene,field, val, data) {
                var input = this.commontext(scene,field, val, data);
                input.append(val);
                return input.prop("outerHTML");
            },
            url: function (scene,field, val, data) {
                var input = this.common(scene,'text', field, val,false, data);;
                input.attr("ng-model", "row." + (field.ng_name?field.ng_name:field.name));
                return input.prop("outerHTML");
            },
            editor: function (scene,field, val) {
                var input = this.commontext(scene,field, val);
                input.addClass("editor");
                return input.prop("outerHTML");
            },
            jsoneditor: function (scene,field, val) {
                var wap = $("<div/>");
                var inputeditor =$('<div  class="json-editor" data-toggle="jsoneditor"></div>')
                wap.append(inputeditor);
                var input = this.commontext(scene,field, val);
                wap.append(input);

                return wap.prop("outerHTML");
            },
            string: function (scene,field, val, data) {
                var input = this.common(scene,'text', field, val,false, data);;
                input.attr("ng-model", "row." + (field.ng_name?field.ng_name:field.name));
                return input.prop("outerHTML");
            },
            number: function (scene,field, val, data) {
                var input = this.common(scene,'text', field, val,false, data);;
                input.attr("ng-model", "row." + (field.ng_name?field.ng_name:field.name));
                return input.prop("outerHTML");
            },
            commondate:function(scene,fmt,field, val) {
                var input = this.common(scene,'text', field, val);
                input.addClass("datetimepicker");
                input.attr("data-date-format", fmt);
                input.attr("data-model", "row." + (field.ng_name?field.ng_name:field.name));
                if (val) {
                    input.val(val);
                }
                var wrap = $('<div class="input-group date datetimepicker">');
                wrap.append(input).append($('<span class="input-group-addon">').append('<span class="fa fa-calendar"></span>'));
                return wrap;
            },
            date: function (scene,field, val) {
                var fmt = field.content != ""?field.content:"YYYY-MM-DD";
                return this.commondate(scene,fmt,field, val).prop("outerHTML");
            },
            time: function (scene,field, val) {
                var fmt = field.content != ""?field.content:"HH:mm:ss";
                return this.commondate(scene,fmt,field, val).prop("outerHTML");
            },
            datetime: function (scene,field, val) {
                var fmt = field.content != ""?field.content:"YYYY-MM-DD HH:mm:ss";
                return this.commondate(scene,fmt,field, val).prop("outerHTML");
            },
            commoncheckbox:function(scene,i, title, type, field, val, m) {
                var input = this.common(scene,type, field, val, m);
                val = val === null || typeof(val) == "undefined" ? [] : ($.isArray(val)? val : val.toString().split(','));
                if($.inArray(i, val) != -1) {
                    input.attr("checked", "checked");
                }
                input.attr("value", i);
                var iname = 'row['+field.name+']';
                if (m) {
                    iname += "[]";
                }
                iname += "-" + i;
                input.attr("id", iname);
                input.removeClass("form-control");
                return $('<label for="' +iname +'">').append(input).append(title);

            },
            commoncheckboxs:function(scene,type, field, val, m) {
                var wap = $("<div/>");
                for(var i in field.content_list) {
                    wap.append(this.commoncheckbox(scene,i,field.content_list[i], type, field, val,m));
                }
                return wap;
            },
            checkbox: function (scene,field, val) {
                return this.commoncheckboxs(scene,'checkbox', field, val,true).prop("outerHTML");
            },
            radio: function (scene,field, val) {
                return $('<div class="radio"/>').append(this.commoncheckboxs(scene,'radio', field, val,false)).prop("outerHTML");
            },

            commonselect:function(scene,field, val, m) {
                var input = $('<select class="selectpicker" '+field.extend+'>');
                input = this.commonattr(scene,input,undefined, field, val, m);
                if (m) {
                    input.attr("multiple", "multiple");
                }
                if ((scene=="add"?field.newstatus:field.editstatus)=='locked') {
                    input.attr("disabled", "disabled");
                }
                input.attr("data-model", "row." + (field.ng_name?field.ng_name:field.name));

                val = val === null || typeof(val) == "undefined" ? [] : ($.isArray(val)? val : val.toString().split(','));
                for(var i in field.content_list) {
                    var o = $('<option>' +field.content_list[i] +'</option>');
                    o.val(i);
                    if ($.inArray(i, val) != -1 || (val.length == 0 && field.defaultvalue == i)) {
                        o.attr("selected", "selected");
                    }
                    input.append(o);
                }
                return input;
            },
            select: function (scene,field, val) {
                return this.commonselect(scene,field, val, false).prop("outerHTML");
            },
            selects: function (scene,field, val) {
                return this.commonselect(scene,field, val, true).prop("outerHTML");
            },
            switch:function(scene,field, val,m) {
                var wap = $("<div/>");
                var input = $("<input  id='c-"+(field.ng_name?field.ng_name:field.name)+"' type='hidden'  autocomplete='off' "+field.extend+"/>");
                if (val) {
                    input.attr("value",val?1:0);
                }
                wap.append(this.commonattr(scene,input,undefined, field, val, m));
                var alink = '<a href="javascript:;" data-toggle="switcher" class="btn-switcher" data-input-id="c-'+(field.ng_name?field.ng_name:field.name)+'" data-yes="1" data-no="0" >'+
                    '<i class="fa fa-toggle-on text-success ' + (!val?"fa-flip-horizontal text-gray":"") + 'fa-2x"></i>'+
                    '</a>';
                wap.append(alink);
                return wap.prop("outerHTML");
            },
            bool: function (scene,field, val) {
                var wap = $("<div/>");
                wap.append(this.commoncheckbox(scene,'yes', 'radio', field, 1));
                wap.append(this.commoncheckbox(scene,'no', 'radio', field, 0));
                return wap.prop("outerHTML");
            },
            commonfile:function(scene,field, val,umimetype,cmimetype,m) {
                var wap = $('<div class="input-group">');
                var input = this.common(scene,'text', field, val);
                input.attr("id", 'c-' + field.name);
                input.attr("readonly", "readonly");
                wap.append(input);

                var inputgroup = $('<div class="input-group-addon no-border no-padding">');

                var button = $('<button  class="btn btn-danger plupload"/>');
                button.attr("type", "button");
                button.attr("id", "plupload-" + field.name);
                button.attr("data-input-id", "c-" + field.name);
                button.attr("data-preview-id", "p-" + field.name);
                if (umimetype) {
                    button.attr("data-mimetype", umimetype);
                }
                button.attr("data-multiple", m?"true":"false");
                if (field.maximum) {
                    button.attr("data-maxcount", field.maximum);
                }
                button.append('<i class="fa fa-upload"></i>上传');
                if (( scene=="add"?field.newstatus:field.editstatus)=='locked') {
                    button.attr("disabled", "disabled");
                }
                inputgroup.append($('<span/>').append(button));

                button = $('<button  class="btn btn-btn-primary fachoose"/>');
                button.attr("type", "button");
                button.attr("id", "fachoose-" + field.name);
                button.attr("data-input-id", "c-" + field.name);
                button.attr("data-preview-id", "p-" + field.name);
                if (cmimetype) {
                    button.attr("data-mimetype", cmimetype);
                }
                button.attr("data-multiple", m?"true":"false");
                if (field.maximum) {
                    button.attr("data-maxcount", field.maximum);
                }
                button.append('<i class="fa fa-fa-list"></i>选择');
                if (( scene=="add"?field.newstatus:field.editstatus)=='locked') {
                    button.attr("disabled", "disabled");
                }
                inputgroup.append($('<span/>').append(button));

                wap.append(inputgroup);
                wap.append('<span class="msg-box n-right" for="c-'+field.name+'"></span>');
                return wap;
            },
            commonimage:function(scene,field, val,m) {
                var wap = $("<div/>");;
                var input = this.commonfile(scene,field, val,"image/gif,image/jpeg,image/png,image/jpg,image/bmp","image/*", m);
                var readhtml = (scene=="add"?field.newstatus:field.editstatus)=='locked'?'readonly="readonly"':'';
                wap.append(input).append('<ul class="row list-inline plupload-preview" '+ readhtml+' id="p-'+field.name+'"></ul>');
                return wap;
            },
            image: function (scene,field, val) {
                return this.commonimage(scene,field, val, false).prop("outerHTML");
            },
            images: function (scene,field, val) {
                return this.commonimage(scene,field, val, true).prop("outerHTML");
            },
            file: function (scene,field, val) {
                return this.commonfile(scene,field, val,undefined,undefined, false).prop("outerHTML");
            },
            files: function (scene,field, val) {
                return this.commonfile(scene,field, val,undefined,undefined, true).prop("outerHTML");
            },
            sound: function (scene,field, val) {
                return this.commonfile(scene,field, val,undefined,undefined, false).prop("outerHTML");
            },
            video: function (scene,field, val) {
                return this.commonfile(scene,field, val,undefined,undefined, false).prop("outerHTML");
            },
            address:function(scene,field, val,m) {
                var wap = $("<div/>");

                var input = this.common(scene,'text', field, val);;
                input.attr("ng-model", "row." + (field.ng_name?field.ng_name:field.name));
                input.attr("readonly", "readonly");
                input.attr("data-toggle", "city-picker");
                input.attr("data-responsive", "true");
                input.attr("width", "100%");
                input.attr("citypicker", "citypicker");

                wap.append(input);
                require(['citypicker'], function () {
                    $('[data-toggle="city-picker"]').citypicker();
                });
                return wap.prop("outerHTML");
            },

            location:function(scene,field, val,m) {
                var wap = $("<div/>");
                var input = $("<location data-name='"+ field.name+"' />");
                input.attr("ng-model", "row." + (field.ng_name?field.ng_name:field.name));
                wap.append(input);
                return wap.prop("outerHTML");
            },

            modelfield:function(scene,field,val, fieldName, m, l) {
                var wap = $("<div class='input-group' modelfield='"+fieldName+"'/>");
                var btn = $('<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat btn-model"><i class="fa fa-list-alt"></i></button></span>');
                wap.append(btn);

                var input = $("<input autocomplete='off' "+field.extend+"/>");
                var attr = {
                    "name":"row[" + fieldName + "]",
                    "data-rule":field.rule,
                    "data-tip":field.tip,
                    "type":"text",
                };
                input.attr(attr);
                if (m) {
                    input.attr("data-multiple", "true");
                }
                if (field.defaultvalue != "") {
                    input.attr("data-source",field.defaultvalue + l);
                }
                if (val && typeof(val[fieldName]) !=  "undefined") {
                    input.attr("value",val[fieldName]);
                }
                if ((val==undefined || scene=="add"?field.newstatus:field.editstatus)=='locked') {
                    input.attr("disabled", "disabled");
                }
                if (((val==undefined || scene=="add")?field.newstatus:field.editstatus)=='hidden') {
                    input.hide();
                }
                input.addClass(" form-control selectpage" + " form-field-" + field.name);
                input.attr("data-and-or", "or");

                wap.append(input);

                var hidden = $('<input type="text" class="sp_hidden hidden_model"  style="display: none;"/>');
                hidden.attr({
                    "ng-model":"row." + fieldName,
                }).val('');
                wap.append(hidden);
                return wap;
            },
            listing:function(scene,field,val) {
                return this.modelfield(scene,field, val,field.name, true,"").prop("outerHTML");
            },
            model:function(scene,field,val) {
                var fieldName = field.name + "_model_id";
                return this.modelfield(scene,field, val,fieldName, false, "/index").prop("outerHTML");
            },
            mztree:function(scene,field,val) {
                var fieldName = field.name + "_model_id";
                var wap = $("<div class='input-group' modelfield='"+fieldName+"'/>");
                var btn = $('' +
                    '<span class="input-group-btn">'+
                    '<button type="button" class="btn btn-default btn-flat btn-model"><i class="fa fa-list-alt"></i></button>'+
                    '</span>');
                wap.append(btn);

                var menuContentUl =$('<ul mztree class="ztree mztree" id="'+fieldName+'"  data-field-name="'+fieldName+'" style="margin-top:0; width:430px; height: 200px;" data-check="check"></ul>');
                if (field.defaultvalue != "") {
                    menuContentUl.attr("data-url",field.defaultvalue + "/ztreelist");
                }
                var menuContentDiv = $('<div id="'+fieldName+'MenuContent" class="menuContent" style="display:none; position: absolute;">');
                menuContentDiv.append(menuContentUl);
                wap.append(menuContentDiv);

                var input = $("<input id='"+fieldName+"Input' ng-click='showFieldMenu(\""+fieldName+"\");' readonly='readonly'  autocomplete='off' "+field.extend+"/>");
                var attr = {
                    "data-rule":field.rule,
                    "data-tip":field.tip,
                    "type":"text",
                };
                input.attr(attr);
                if (val && typeof(val[fieldName]) !=  "undefined") {
                    var valist = [];
                    $.each(val[field.defaultvalue], function(k,v){
                        valist.push(v.name);
                    });
                    input.attr("value",valist.join(","));
                }
                if ((val==undefined || scene=="add"?field.newstatus:field.editstatus)=='locked') {
                    input.attr("disabled", "disabled");
                }
                if (((val==undefined || scene=="add")?field.newstatus:field.editstatus)=='hidden') {
                    input.hide();
                }
                input.addClass("form-control" + " form-field-" + field.name);

                wap.append(input);

                var hidden = $('<input id="'+fieldName+'HiddenModel" type="text" class="sp_hidden hidden_model"  style="display: none;"/>');
                hidden.attr({
                    "ng-model":"row." + fieldName,
                    "name":"row[" + fieldName + "]",
                }).val('');
                if (val && typeof(val[fieldName]) !=  "undefined") {
                    hidden.attr("value",val[fieldName]);
                }
                wap.append(hidden);
                return wap.prop("outerHTML");
            },

            idcode:function(scene,field,val) {
                var wap = $("<div class='input-group'/>");
                var input = $("<input placeholder='点击获取新的编号或手动输入' type='text'"+field.extend+"/></div>");
                input = this.commonattr(scene,input, undefined, field, val, false);
                input.attr("ng-model", "row." + field.name);
                if (val) {
                    input.attr("value",val);
                }
                wap.append(input);

                var btn = $('<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat btn-idcode-gift" ng-click="giftIdcode()"><i class="fa  fa-gift"></i></button></span>');
                wap.append(btn);

                return wap.prop("outerHTML");
            },
            cascader:function(scene,field,val) {
                var cascaderid = field.name + "_cascader_id";
                var cascaderidkeyword = field.name + "_cascader_keyword";

                var wap = $("<div/>");
                var input = $("<input readonly='readonly' style='cursor: hand' class='cascader  form-control ' autocomplete='off' type='text' "+field.extend+" />");

                var attr = {
                    "data-rule":field.rule,
                    "data-tip":field.tip,
                    "data-model":"row." + cascaderid,
                    "data-keyword-model":"row." + cascaderidkeyword,
                    "data-field-name":field.name,
                };
                input.attr(attr);

                if (field.defaultvalue != "") {
                    input.attr("data-source",field.defaultvalue + "/cascader");
                }

                if ((val==undefined || scene=="add"?field.newstatus:field.editstatus)=='locked') {
                    input.attr("disabled", "disabled");
                }
                wap.append(input);

                var hidden = $('<input type="hidden"/>');
                hidden.attr({
                    "ng-model":"row." + cascaderidkeyword,
                    "name":"row[" + cascaderidkeyword + "]",
                }).val('');
                if (val && typeof(val[cascaderid]) !=  "undefined") {
                    hidden.val(val[cascaderidkeyword]);
                }
                wap.append(hidden);

                var hidden_model = $('<input type="text" class="sp_hidden hidden_model"  style="display: none;"/>');
                hidden_model.attr({
                    "name":"row[" + cascaderid + "]",
                    "ng-model":"row." + cascaderid,
                }).val('');
                wap.append(hidden_model);
                return wap.prop("outerHTML");
            },
            sortable:function(scene,field,val) {
                var iname = field['raw_name']?field['raw_name']:"row[" + field.name + "]";
                var ul = $("<ul class='sortable-list sortable-list-tags' sortable data-group='"+field.name+"'/>");
                var attr = {
                    "name":iname,
                    "data-rule":field.rule,
                    "data-tip":field.tip,
                };
                ul.attr(attr);
                angular.forEach(field.content_list, function(item,k){
                    var li = $("<li data-id='"+k+"'/>");
                    li.html(item);
                    ul.append(li);
                });
                return ul.prop("outerHTML");
            }
        }
    };
    return Form;
});