define(['jquery', 'bootstrap', 'fast', 'customer', 'form','layer'], function ($, undefined, Fast, Customer, Form,Layer) {

    var Controller = {
        init: function () {
        },

        index: function () {
            $(".btn-pre-signin").on("click", function(){
                var self = $(this);
                Controller.api.presign(self.data("id")).then(function(){
                    window.location.reload();
                });
            });

            $(".btn-leave").on("click", function(){
                var self = $(this);
                Controller.api.leave(self.data("id"), self.data("leave-off-count")).then(function(){
                    window.location.reload();
                });
            });

            $(".btn-unstate").on("click", function(){
                $.alert("教师还未结课");
            });
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