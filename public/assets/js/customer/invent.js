define(['jquery', 'bootstrap', 'fast', 'customer', 'form','flexible'], function ($, undefined, Fast, Customer, Form, undefined) {

    var Controller = {
        init: function () {
        },

        index:function() {


        },
        edit:function() {
            Controller.api.bindevent();
        },

        add:function() {
            Controller.api.bindevent();
        },

        api: {
            bindevent: function () {
                Form.api.bindevent($("#form"), function(data, ret){
                    setTimeout(function(){window.location.replace(ret.url);}, 1000);
                });
            }
        }
    };
    Controller.api = $.extend(Customer.api, Controller.api);
    Controller.init();

    return Controller;
});
