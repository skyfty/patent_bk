define(['jquery', 'bootstrap', 'fast', 'customer', 'form','layer'], function ($, undefined, Fast, Customer, Form,Layer) {

    var Controller = {
        init: function () {
        },

        index: function () {

        },


        evaluate:function(){
            require(['raty'], function(){
                $('.raty').each(function(){
                    var self = $(this);
                    var score = self.data(score);
                    self.raty({ starType: 'i',readOnly:true,score:score });
                });
            });

            require(['swiper'], function(){
                $('.campaign').on("click", function(){
                    var mysrc = $(this).data("full");
                    var items = [];
                    var initIndex = 0;
                    $(".campaign").map(function(n, i){
                        var src = $(this).data("full");
                        items.push(src);
                        if (mysrc == src) {
                            initIndex = n;
                        }
                    });
                    $.photoBrowser({
                        items: items,
                        initIndex:initIndex
                    }).open(); ;
                });
            });

            if (typeof Config.wxsdk != 'undefined' && Config.wxsdk) {
                require(['jssdk'], function(wx){
                    wx.config(Config.wxsdk);
                    wx.ready(function () {
                        var shareParams = {
                            title: row.shared.title,
                            imgUrl:  row.shared.imgUrl,
                            desc: row.shared.desc,
                            link: row.shared.link
                        };
                        wx.onMenuShareAppMessage(shareParams);
                    });
                });
            }

            $("a.lore-item").on("click", function(){
                $(".lore-description").hide();
                $(".lore-description", this).show();
            });

            $("#btn-share").on("click", function(){
                Controller.api.share();
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