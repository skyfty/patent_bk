define(['wechat', 'template', 'moment', 'fast'], function (Wechat, Template, Moment, Fast) {
    var Customer = {
        api: {
            loadlist: function (url, custom, offset,limit) {
                var self = this;
                var deferred = $.Deferred();
                $.extend(custom, {
                    'offset': offset,
                    'limit': limit,
                });
                $.ajax({type: "GET", url:url,
                    data: custom
                }).then(function(ret){
                    if (ret && ret.rows && ret.rows.length > 0) {
                        deferred.resolve(ret);
                    }
                });
                return deferred.promise();
            },
            iswx: function() {
                return navigator.userAgent.toLowerCase().indexOf('micromessenger') != -1;
            },
            share: function(){
                var sharetpl='<div class="weui-share" onclick="$(this).remove();">\n' +
                    '<div class="weui-share-box">\n' +
                    '点击右上角发送给指定朋友或分享到朋友圈 <i></i>\n' +
                    '</div>\n' +
                    '</div>';
                $("body").append(sharetpl);
            },
            presign:function(id) {
                var deferred = $.Deferred();
                $.modal({
                    title: "预签到！",
                    text: "宝宝进行预签到后，无论宝宝是否正常参加授课，均会扣除课次。进行预签到将会增加宝宝的智慧点2点。您确认吗？",
                    buttons: [
                        { text: "是", onClick: function(){
                            Customer.api.ajax({url:"/provider/presignin",data:{
                                id:id
                            }}, function(data, ret){
                                if (ret.code == 1) {
                                    deferred.resolve(ret);
                                }
                            });
                        } },
                        { text: "否", className: "default"},
                    ]
                });
                return deferred.promise();
            },
            leave:function(id, cnt) {
                var deferred = $.Deferred();
                $.modal({
                    title: "请假",
                    text: "当前您剩余"+cnt+"次“可请假次数”，进行请假需要1次“可请假次数”，您是否确认请假？",
                    buttons: [
                        { text: "是", onClick: function(){
                            Customer.api.ajax({url:"/provider/leave",data:{
                                id:id
                            }}, function(data, ret){
                                if (ret.code == 1) {
                                    deferred.resolve(ret);
                                }
                            });
                        } },
                        { text: "否", className: "default"},
                    ]
                });
                return deferred.promise();
            },

            wxReady: function(wx){
                var shareddesc = $("#shared-desc").html();
                if (shareddesc == "" || typeof shareddesc == "undefined") {
                    shareddesc = Config.shared.desc;
                }
                var sharedtitle = $("#shared-title").html();
                if (sharedtitle == "" || typeof sharedtitle == "undefined") {
                    sharedtitle = Config.shared.title;
                }
                if (Config.shared.link == "") {
                    Config.shared.link = window.location.href;
                }
                var shareParams = {
                    title: sharedtitle,
                    imgUrl:  Config.moduleurl + Config.shared.imgUrl,
                    desc: shareddesc,
                    link:Config.shared.link
                };
                wx.onMenuShareAppMessage(shareParams); wx.onMenuShareTimeline(shareParams);
            },

            wxpay:function(unifiedOrder, callback) {
                if (typeof Config.wxsdk != 'undefined' && Config.wxsdk) {
                    require(['jssdk'], function(wx){
                        wx.ready(function () {
                            unifiedOrder.complete = callback;
                            wx.chooseWXPay(unifiedOrder);
                        });
                    });
                }
            }
        },
        init: function () {
            $("#form").data("validator-options", {
                invalid: function (form, errors) {
                    $.each(errors, function (i, j) {
                        Toastr.error(j);
                    });
                },
                target: '#errtips'
            });

            if (typeof Config.wxsdk != 'undefined' && Config.wxsdk) {
                require(['jssdk'], function(wx){
                    wx.config(Config.wxsdk);
                    wx.ready(function () {
                        Customer.api.wxReady(wx);
                    });
                });
            }
        },

    };
    Customer.api = $.extend(Wechat.api, Customer.api);
    //将Template渲染至全局,以便于在子框架中调用
    window.Template = Template;
    //将Moment渲染至全局,以便于在子框架中调用
    window.Moment = Moment;
    //将Staff渲染至全局,以便于在子框架中调用
    window.Customer = Customer;

    Customer.init();
    return Customer;
});
