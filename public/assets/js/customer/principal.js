define(['jquery', 'bootstrap', 'fast', 'customer', 'form','flexible'], function ($, undefined, Fast, Customer, Form, undefined) {

    var Controller = {
        init: function () {
        },

        index:function() {
            $("#add-principal").on("click", function(){
                $("#principal-class").popup();
            });

            require(['flexible', 'iscroll', 'navbarscroll'], function(flexible, IScroll, navbarscroll){
                window.IScroll = IScroll;
                $('#retr').navbarscroll({
                    endClickScroll:function(thisObj){
                        var id = $("#retr li.cur a").data("id");
                        $.ajax({ url:"/principal/view", data:{ id:id, },
                            success:function(ret) {
                                if (ret && ret.code == 0 && ret.data) {
                                    var template_name = "principal-tmpl-" + ret.data.substance_type;
                                    var html = Template(template_name,ret.data);
                                    $("#principal-body").html(html);
                                    $("a.principal-delete").on("click", function(){
                                        var id = $(this).data("id");
                                        $.confirm({
                                            title: '删除主体',
                                            text: '确定要删除这个主体吗?',
                                            onOK: function () {
                                                $.ajax({url: "/principal/del", data: {ids: id},
                                                        success: function (ret) {
                                                            window.location.reload();
                                                        }
                                                    }
                                                )
                                            }
                                        });
                                    });
                                } else {
                                    Toastr.error("获取数据失败");
                                }
                            }
                        });
                    }
                });
                var id = Fast.api.query("id");
                if (id) {
                    $('a[data-id="'+id+'"]').trigger("click");
                }
            });

        },
        edit:function() {
            Controller.api.bindevent();
        },

        add:function() {
            Controller.api.bindevent();
        },

        api: {
            bindevent: function () {
                require(['jquery-weui-city-picker'], function(){
                    $(".weui-address-pick").cityPicker({
                    });
                });
                Form.api.bindevent($("#form"), function(data, ret){
                    Toastr.success(ret.msg);
                    setTimeout(function(){window.location.replace(ret.url);}, 1000);

                });
            }
        }
    };
    Controller.api = $.extend(Customer.api, Controller.api);
    Controller.init();

    return Controller;
});