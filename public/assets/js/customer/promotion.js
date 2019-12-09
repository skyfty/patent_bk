define(['jquery', 'bootstrap', 'fast', 'customer', 'form','template','swiper'], function ($, undefined, Fast, Customer, Form, Template, Swiper) {

    var Controller = {
        init: function () {
            console.log("lksdjf");
        },
        view: function () {
            var swiper = new Swiper('.item-img', {
                autoplay:true,
                delay: 7000,
                slidesPerView: 1,
                spaceBetween: 0,
                keyboard: {
                    enabled: true,
                },
                pagination: {
                    el: '.swiper-pagination',
                    type: 'fraction',
                },
            });
            var MAX = 10, MIN = 1;
            $('.weui-count__decrease').click(function (e) {
                var $input = $(e.currentTarget).parent().find('.weui-count__number');
                var number = parseInt($input.val() || "0") - 1
                if (number < MIN) number = MIN;
                $input.val(number)
            });
            $('.weui-count__increase').click(function (e) {
                var $input = $(e.currentTarget).parent().find('.weui-count__number');
                var number = parseInt($input.val() || "0") + 1
                if (number > MAX) number = MAX;
                $input.val(number)
            });
        },
        index: function () {

        },

        api: {
            bindevent: function () {
            }
        }
    };
    Controller.api = $.extend(Customer.api, Controller.api);
    Controller.init();

    return Controller;
});