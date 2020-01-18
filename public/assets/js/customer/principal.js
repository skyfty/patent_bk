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
            require(['jquery-weui-city-picker'], function(){
                $(".weui-address-pick").cityPicker({
                });
            });


            Form.api.bindevent($("#update-form-myavatar"), function(){
                return false;
            });
        },

        add:function() {
            require(['jquery-weui-city-picker'], function(){
                $(".weui-address-pick").cityPicker({
                });
            });


            Form.api.bindevent($("#update-form-myavatar"), function(){
                return false;
            });
        },

        defaultact:function() {
            Form.api.bindevent($("#form"), function(){
                $.toast("修改成功");
                return false;
            });
        },
        api: {
        }
    };
    Controller.api = $.extend(Customer.api, Controller.api);
    Controller.init();

    return Controller;
});