define(['jquery', 'bootstrap', 'fast', 'customer', 'form','template','swiper'], function ($, undefined, Fast, Customer, Form, Template, Swiper) {

    var Controller = {
        init: function () {
        },
        view: function () {

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