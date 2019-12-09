define(['jquery', 'bootstrap', 'customer', 'form', 'swiper'], function ($, undefined, Customer, Form, Swiper) {
    var Controller = {
        index: function () {
            // 顶部轮播图
            var mySwiper = new Swiper ('.swiper-container', {
                autoplay:true,
                pagination: {
                    el: '.swiper-pagination'
                },
                speed: 1200,
            });
        },
        login: function () {
            if (Controller.api.iswx()) {
                location.href = "/index/wxoauth?url=" + (redirect_url?redirect_url:"/provider/index");
                return;
            }
            $("#login").show();
            $("#logining").hide();

            Form.api.bindevent($("#form"), function (data) {
                localStorage.setItem("lastlogin", JSON.stringify({
                    id: data.id,
                    username: data.username,
                    avatar: data.avatar
                }));
                var url = data.url;
                if (url == "") {
                    url = redirect_url?redirect_url:"/provider/index";
                }
                location.href = Controller.api.fixurl(url);
            });
        },
        api: {
        },
        init: function () {

        }
    };
    Controller.api = $.extend(Customer.api, Controller.api);

    return Controller;
});