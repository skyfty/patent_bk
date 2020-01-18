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
