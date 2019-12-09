define(['jquery', 'bootstrap', 'fast', 'customer', 'form', 'swiper'], function ($, undefined, Fast, Customer, Form, Swiper) {

    var Controller = {
        init: function () {
            Controller.api.bindevent();
        },
        index: function () {
            var swiper = new Swiper('.swiper-container', {
                slidesPerView: 5,
            });

            var loadlistidx = 0;
            $("#load-more").on("click", function(){
                var state = $("li.swiper-slide.tab-active").data("state");
                var param = {
                    "state":state
                };
                Controller.api.loadlist("/trade/index", param, loadlistidx, 5).then(function(ret){
                    for (var i = 0; i < ret.rows.length; ++i) {
                        $(".tab-panel-item").append(Template("item-tmpl",ret.rows[i]));
                    }

                    if (ret.rows.length > 0) {
                        $(".aui-order-pay").on("click", function(){
                            var id = $(this).data("id");
                            Controller.api.pay(id, function (res) {
                                window.location.replace("/trade/view/id/" + id);
                            });
                            return false;
                        });
                        Controller.api.bindevent();
                    }
                    loadlistidx += ret.rows.length;
                });
            });

            $("li.swiper-slide").on("click", function(){
                loadlistidx = 0;
                $(".swiper-wrapper li").removeClass("tab-active");
                $(this).addClass("tab-active");
                $(".tab-panel-item").html("");
                $("#load-more").trigger("click");
            });
            $("li.swiper-slide.tab-active").trigger("click");


        },
        view: function () {
            if (row.status == "done" && row.usestatus != 'used' && customer_ids != null && customer_ids.length == 1) {
                Controller.api.expend(customer_ids[0]);
            }

            $(".aui-order-pay").on("click", function(){
                var id = $(this).data("id");
                Controller.api.pay(id, function (res) {
                    window.location.reload();
                });
                return false;
            });
        },


        consume: function () {
            $(".btn-customer").on("click", function(){
                var customer_id = $(this).data("customer");
                Controller.api.expend(customer_id);
                return false;
            });
        },
        add: function () {
            $("#btn-pay").on("click", function(){
                if (!Fast.api.iswx()) {
                    alert("请在微信中打开此页面， 并完成支付");
                    return false;
                }
                Fast.api.ajax({
                    url: "trade/add",
                    data: {
                        commodity_id: commodity.id,
                        amount: amount
                    }
                }, function (data, ret) {
                    Controller.api.wxpay(data.unifiedOrder,function (res) {
                        window.location.replace("/trade/view/id/" + data.id);
                    });
                    return false;
                });
                return false;
            });
        },
        api: {

            expend:function(customer_id) {
                $.modal({
                    title: "使用课次",
                    text: "确定要给学员充值课次吗？",
                    buttons: [
                        { text: "是", onClick: function(){
                            Fast.api.ajax({
                                url: "trade/consume",
                                type:"post",
                                data: {
                                    id: row.id,
                                    customer_model_id: customer_id,
                                }
                            }, function (data, ret) {
                                window.location.replace("/student/index/id/" +customer_id);
                                return false;
                            });
                        } },
                        { text: "否", className: "default"},
                    ]
                });
            },

            pay:function(id, cb) {
                if (!Fast.api.iswx()) {
                    alert("请在微信中打开此页面， 并完成支付");
                    return false;
                }
                var defer = $.Deferred();

                Fast.api.ajax({
                    url: "trade/pay",
                    data: {
                        id: id,
                    }
                }, function (data, ret) {
                    Controller.api.wxpay(data.unifiedOrder,cb);
                    return false;
                });
                return defer;
            },

            bindevent:function() {
                $(".aui-order-cancel").on("click", function(){
                    var id = $(this).data("id");
                    $.modal({
                        title: "取消订单",
                        text: "确定要取消些课次订单吗？",
                        buttons: [
                            { text: "是", onClick: function(){
                                Fast.api.ajax({
                                    url: "trade/cancel",
                                    data: {
                                        id: id,
                                    }
                                }, function (data, ret) {
                                    window.location.reload();
                                    return false;
                                });
                            } },
                            { text: "否", className: "default"},
                        ]
                    });
                    return false;
                });

                $(".aui-order-use").on("click", function(){
                    var id = $(this).data("id");
                    window.location.replace("/trade/consume/id/" + id);
                    return false;
                });
            }
        }
    };
    Controller.api = $.extend(Customer.api, Controller.api);
    Controller.init();

    return Controller;
});