define(['jquery', 'bootstrap', 'fast', 'customer', 'form'], function ($, undefined, Fast, Customer, Form) {

    var Controller = {
        init: function () {
        },
        index: function () {

        },

        edit:function() {
            $("#delete-address").click(function(){
                var id = $(this).data("address-id");
                $.confirm("您确定要删除此地址吗?", "确认删除?", function() {
                    Controller.api.ajax({
                        url:"/contact/del",
                        data:{ids:id}
                    }, function(){
                        location.href = "/contact/index";
                    });
                });
            });
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                require(['citypicker'], function(){
                    $("#address").cityPicker();
                });
                Form.api.bindevent($("#form"), function(){
                    location.href = "/contact/index";
                });
            }
        }
    };
    Controller.api = $.extend(Customer.api, Controller.api);
    Controller.init();

    return Controller;
});