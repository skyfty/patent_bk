define([], function () {
    require([], function () {
    //绑定data-toggle=addresspicker属性点击事件

    $(document).on('click', "[data-toggle='addresspicker']", function () {
        var that = this;
        var callback = $(that).data('callback');
        var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";
        var lat_id = $(that).data("lat-id") ? $(that).data("lat-id") : "";
        var lng_id = $(that).data("lng-id") ? $(that).data("lng-id") : "";
        var lat = lat_id ? $("#" + lat_id).val() : '';
        var lng = lng_id ? $("#" + lng_id).val() : '';
        var url = "/addons/address/index/select";
        url += (lat && lng) ? '?lat=' + lat + '&lng=' + lng : '';
        Fast.api.open(url, '位置选择', {
            callback: function (res) {
                input_id && $("#" + input_id).val(res.address);
                lat_id && $("#" + lat_id).val(res.lat);
                lng_id && $("#" + lng_id).val(res.lng);
                try {
                    //执行回调函数
                    if (typeof callback === 'function') {
                        callback.call(that, res);
                    }
                } catch (e) {

                }
            }
        });
    });
});

require.config({
    paths: {
        'fullcalendar': '../libs/fullcalendar/dist/fullcalendar',
        'fullcalendar-lang': '../libs/fullcalendar/dist/locale/zh-cn',
    },
    // shim依赖配置
    shim: {
        'fullcalendar-lang': ['fullcalendar'],
    },
});
if ($('.cropper', $('form[role="form"]')).length > 0) {
    var allowAttr = [
        'aspectRatio', 'autoCropArea', 'cropBoxMovable', 'cropBoxResizable', 'minCropBoxWidth', 'minCropBoxHeight', 'minContainerWidth', 'minContainerHeight',
        'minCanvasHeight', 'minCanvasWidth', 'croppedWidth', 'croppedHeight', 'croppedMinWidth', 'croppedMinHeight', 'croppedMaxWidth', 'croppedMaxHeight', 'fillColor'
    ];
    String.prototype.toLineCase = function () {
        return this.replace(/[A-Z]/g, function (match) {
            return "-" + match.toLowerCase();
        });
    };

    var btnAttr = [];
    $.each(allowAttr, function (i, j) {
        btnAttr.push('data-' + j.toLineCase() + '="<%=data.' + j + '%>"');
    });
    var btn = '<button class="btn btn-success btn-cropper btn-xs" data-input-id="<%=data.inputId%>" ' + btnAttr.join(" ") + ' style="position:absolute;top:10px;right:15px;">裁剪</button>';
    require(['upload'], function (Upload) {
        //图片裁剪
        $(document).on('click', '.btn-cropper', function () {
            var image = $(this).closest("li").find('.thumbnail').data('url');
            var input = $("#" + $(this).data("input-id"));
            var url = image;
            var data = $(this).data();
            var params = [];
            $.each(allowAttr, function (i, j) {
                if (typeof data[j] !== 'undefined' && data[j] !== '') {
                    params.push(j + '=' + data[j]);
                }
            });
            (parent ? parent : window).Fast.api.open('/addons/cropper/index/cropper?url=' + image + (params.length > 0 ? '&' + params.join('&') : ''), '裁剪', {
                callback: function (data) {
                    if (typeof data !== 'undefined') {
                        var arr = data.dataURI.split(','), mime = arr[0].match(/:(.*?);/)[1],
                            bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
                        while (n--) {
                            u8arr[n] = bstr.charCodeAt(n);
                        }
                        var urlArr = url.split('.');
                        var suffix = 'png';
                        url = urlArr.join('');
                        var filename = url.substr(url.lastIndexOf('/') + 1);
                        var exp = new RegExp("\\." + suffix + "$", "i");
                        filename = exp.test(filename) ? filename : filename + "." + suffix;
                        var file = new File([u8arr], filename, {type: mime});
                        Upload.api.send(file, function (data) {
                            input.val(input.val().replace(image, data.url)).trigger("change");
                        }, function (data) {
                        });
                    }
                },
                area: ["880px", "520px"],
            });
            return false;
        });

        var insertBtn = function () {
            return arguments[0].replace(arguments[2], btn + arguments[2]);
        };
        Upload.config.previewtpl = Upload.config.previewtpl.replace(/<li(.*?)>(.*?)<\/li>/, insertBtn);
        $(".cropper").each(function () {
            var preview = $("#" + $(this).data("preview-id"));
            if (preview.size() > 0 && preview.data("template")) {
                var tpl = $("#" + preview.data("template"));
                tpl.text(tpl.text().replace(/<li(.*?)>(.*?)<\/li>/, insertBtn));
            }
        });
    });
}
if ($('.kdniao').length > 0) {

    $('.kdniao').each(function () {
        var code = $(this).data('code');

        $(this).addClass('btn btn-xs bg-success').append('<i class="fa fa-truck"></i>' + code);
    });

    $('.kdniao').click(function () {
        var company = $(this).data('company');
        var code = $(this).data('code');

        if (company && code) {
            Layer.open({
                type: 2,
                area: ['700px', '450px'],
                fixed: false, //不固定
                maxmin: true,
                content: '/addons/kdniao/index/query?company=' + company + '&code=' + code
            });
        }
    });
}
require.config({
    paths: {
        'summernote': '../addons/summernote/lang/summernote-zh-CN.min'
    },
    shim:{
        'summernote': ['../addons/summernote/js/summernote.min', 'css!../addons/summernote/css/summernote.css'],
    }
});
require(['form', 'upload'], function (Form, Upload) {
    var _bindevent = Form.events.bindevent;
    Form.events.bindevent = function (form) {
        _bindevent.apply(this, [form]);
        try {
            //绑定summernote事件
            if ($(".summernote,.editor", form).size() > 0) {
                require(['summernote'], function () {
                    $(".summernote,.editor", form).summernote({
                        height: 250,
                        lang: 'zh-CN',
                        fontNames: [
                            'Arial', 'Arial Black', 'Serif', 'Sans', 'Courier',
                            'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande',
                            "Open Sans", "Hiragino Sans GB", "Microsoft YaHei",
                            '微软雅黑', '宋体', '黑体', '仿宋', '楷体', '幼圆',
                        ],
                        fontNamesIgnoreCheck: [
                            "Open Sans", "Microsoft YaHei",
                            '微软雅黑', '宋体', '黑体', '仿宋', '楷体', '幼圆'
                        ],
                        toolbar: [
                            ['style', ['style', 'undo', 'redo']],
                            ['font', ['bold', 'underline', 'strikethrough', 'clear']],
                            ['fontname', ['color', 'fontname', 'fontsize']],
                            ['para', ['ul', 'ol', 'paragraph', 'height']],
                            ['table', ['table', 'hr']],
                            ['insert', ['link', 'picture', 'video']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        dialogsInBody: true,
                        callbacks: {
                            onChange: function (contents) {
                                $(this).val(contents);
                                $(this).trigger('change');
                            },
                            onInit: function () {
                            },
                            onImageUpload: function (files) {
                                var that = this;
                                //依次上传图片
                                for (var i = 0; i < files.length; i++) {
                                    Upload.api.send(files[i], function (data) {
                                        var url = Fast.api.cdnurl(data.url);
                                        $(that).summernote("insertImage", url, 'filename');
                                    });
                                }
                            }
                        }
                    });
                });
            }
        } catch (e) {

        }

    };
});

});