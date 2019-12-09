define(['jquery', 'bootstrap', 'fast', 'customer', 'form'], function ($, undefined, Fast, Customer, Form) {

    var Controller = {
        init: function () {
        },

        register:function(){
            Form.api.bindevent($("#form"), function(){
                $.toast("绑定成功", function() {
                    location.href = "/user/index";
                });
                return false;
            });
        },

        index:function() {
            $("#user-withdraw").click(function(){
                if (customer.banknumber == "") {
                    $.modal({
                        title: "警告！",
                        text: "您还没有绑定银行卡!!",
                        buttons: [
                            { text: "去绑定", onClick: function(){
                                location.href = "/user/bank";
                            } },
                            { text: "取消", className: "default"},
                        ]
                    });
                    return;
                }
                location.href = "/user/withdraw";
            });

            $("#user-qrcode").click(function(){
                var idx = Fast.api.open("/user/shared","", { maxmin: false});
                Layer.full(idx);
            });

            $("#plupload-avatar").data("upload-success", function (data) {
                var url = Controller.api.cdnurl(data.url);
                $(".profile-user-img").prop("src", url);
                $("#c-avatar").val(data.url);
                $("#update-form-myavatar").submit();
            }).click(function(){
                var self = this;
                require(['jssdk'], function(wx){
                    wx.config(Config.wxsdk);
                    wx.ready(function () {
                        wx.chooseImage({
                            count: 1,
                            sizeType: ['original', 'compressed'],
                            sourceType: ['album', 'camera'],
                            success: function (res) {
                                wx.getLocalImgData({
                                    localId: res.localIds[0], // 图片的localID
                                    success: function (res) {
                                        Fast.api.ajax({
                                            url:"/ajax/upload_base64",
                                            data:{
                                                file:res.localData
                                            }
                                        }, function(data, ret){
                                            $(self).data("upload-success")(data);
                                            return false;
                                        });
                                    },
                                    fail: function (res) {
                                        alert(JSON.stringify(res));
                                    }
                                });
                            },
                            fail: function (res) {
                                alert(JSON.stringify(res));
                            }
                        });
                    });
                });
                return false;
            });
            Form.api.bindevent($("#update-form-myavatar"), function(){
                return false;
            });

            if (Config.wechat.agreement == 1 && customer.agreement == 0) {
                $.modal({
                    title: "协议",
                    text: "您还没有签署服务协议，只有签署后才能支付购买课次!!",
                    buttons: [
                        { text: "签署", onClick: function(){
                            location.href = "/user/agreement";
                        } },
                        { text: "取消", className: "default"},
                    ]
                });
            }
        },
        bank:function() {
            Form.api.bindevent($("#form"), function(){
                $.toast("绑定成功", function() {
                    location.href = "/user/index";
                });
                return false;
            });
        },
        agreement:function() {
            Form.api.bindevent($("#form"), function(){
                $.toast("签署完成", function() {
                    location.href = "/user/index";
                });
                return false;
            });
        },
        address:function() {
            require(['citypicker'], function(){
                $("#address").cityPicker();
            });
            Form.api.bindevent($("#form"), function(){
                $.toast("修改成功", function() {
                    location.href = "/user/index";
                });
                return false;
            });
        },
        defaultact:function() {
            Form.api.bindevent($("#form"), function(){
                $.toast("修改成功", function() {
                    location.href = "/user/index";
                });
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