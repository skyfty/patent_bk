define(['jquery', 'bootstrap', 'fast', 'customer', 'form','flexible'], function ($, undefined, Fast, Customer, Form, undefined) {

    var Controller = {
        init: function () {
        },

        index:function() {
            $("#plupload-avatar").data("upload-success", function (data) {
                var url = Controller.api.cdnurl(data.url);
                $(".profile-user-img").prop("src", url);
                $("#c-avatar").val(data.url);
                $("#update-form-myavatar").submit();
            }).click(function(){
                var self = this;
                require(['jssdk'], function(wx){
                    wx.config(Config.wxsdk);
                    wx.ready(function () {
                        wx.chooseImage({
                            count: 1,
                            sizeType: ['original', 'compressed'],
                            sourceType: ['album', 'camera'],
                            success: function (res) {
                                wx.getLocalImgData({
                                    localId: res.localIds[0], // 图片的localID
                                    success: function (res) {
                                        Fast.api.ajax({
                                            url:"/ajax/upload_base64",
                                            data:{
                                                file:res.localData
                                            }
                                        }, function(data, ret){
                                            $(self).data("upload-success")(data);
                                            return false;
                                        });
                                    },
                                    fail: function (res) {
                                        alert(JSON.stringify(res));
                                    }
                                });
                            },
                            fail: function (res) {
                                alert(JSON.stringify(res));
                            }
                        });
                    });
                });
                return false;
            });

            $("div.knowledge-tab ul.tab-nav li.tab-nav-item").on("click", function(){
                $("div.knowledge-tab ul.tab-nav li").removeClass("tab-active");
                $(this).addClass("tab-active");
                $("div.knowledge-tab .tab-panel .tab-panel-item").removeClass("tab-active");
                var idx = $(this).index();
                $("div.knowledge-tab .tab-panel div:nth-child("+(idx+1)+")").addClass("tab-active");
            });

            require(['echarts','echarts-theme'], function (Echarts,undefined) {
                $(".echarts").each(function(){
                    var chartdiv = $(this);
                    var amounttip = chartdiv.data("amount-tip");
                    var total = chartdiv.data("total");
                    var amount = chartdiv.data("amount");
                    var totaltip = chartdiv.data("total-tip");
                    var unused = total - amount;
                    var myChart = Echarts.init(chartdiv[0], 'walden');
                    var option = {
                        title: {
                            text: '',
                            left: 'right',
                            top: 20,
                            textStyle: {
                                color: '#ccc'
                            }
                        },
                        legend: {
                            orient: 'vertical',
                            x: 'left',
                            data:[
                                '总数 '  + total,
                                amounttip + amount
                            ]
                        },
                        series : [
                            {
                                type: 'pie',
                                radius : '55%',
                                center: ['50%', '50%'],
                                data: [
                                    {
                                        name:totaltip + unused,
                                        value:unused
                                    },
                                    {
                                        name:amounttip + amount,
                                        value:amount
                                    }
                                ],
                            }
                        ]
                    };
                    myChart.setOption(option);
                });

                function refreshAcquire(){
                    var sendstate = false;
                    var retr = $("#retr li.cur a");
                    var genreid = retr.data("genre-id");
                    var obtain = $("#retr2 li.cur a").data("obtain");
                    if (!genreid || typeof obtain == "undefined" || sendstate)
                        return;

                    var customerid = retr.data("customer-id");
                    Fast.api.ajax({
                        url:"/acquire/index",
                        data:{
                            genre_id:genreid,
                            customer_id:customerid,
                            obtain:obtain
                        },
                        beforeSend:function(){
                            sendstate = true;
                        },
                        complete:function(){
                            sendstate = false;
                        },
                    }, function (lorerange,ret) {
                        var list = [];
                        for(var i in lorerange) {
                            var fetch = lorerange[i].fetch?lorerange[i].fetch:0;
                            var amount = lorerange[i].amount;
                            var title = lorerange[i].name;
                            if (lorerange[i].capital) {
                                title += lorerange[i].capital;
                            }
                            list.push({
                                scale:fetch / amount * 100,
                                name:fetch + "/" + amount,
                                title:title,
                                customer_id:customerid,
                                lorerange_id:lorerange[i].id,
                                obtain:lorerange[i].obtain,
                                lastcnt:lorerange[i].lastcnt

                            });
                        }
                        $("#list-acquire").html(Template("progress-tmpl",{list:list}));
                        return false;
                    });
                }

                require(['flexible', 'iscroll', 'navbarscroll'], function(flexible, IScroll, navbarscroll){
                    window.IScroll = IScroll;
                    $('#retr').navbarscroll({
                        endClickScroll:function(thisObj){
                            var genreid = $("#retr li.cur a").data("genre-id");
                            $.ajax({ url:"/genre/obtain", data:{ id:genreid, },
                                success:function(ret) {
                                    var htmll=[];
                                    for (var d in ret) {
                                        htmll.push("<li><a href='javascript:void(0)'  class='btn-scroller'  data-obtain='"+d+"'>"+ret[d]+"</a> </li>");
                                    }
                                    $("#obtainlist").html(htmll.join(''));

                                    $('#retr2').navbarscroll({
                                        endClickScroll:function(thisObj){
                                            refreshAcquire();
                                        }
                                    });
                                }
                            });
                        }
                    });

                    $('a[data-genre-id="3"]').trigger("click");
                    $('a[data-age="'+$("#retr2").data("customer-age")+'"]').trigger("click");
                });
            });
            $(".btn-pre-signin").on("click", function(){
                var self = $(this);
                Controller.api.presign(self.data("id")).then(function(){
                    window.location.reload();
                });
            });

            Form.api.bindevent($("#update-form-myavatar"), function(){
                return false;
            });
        },

        defaultact:function() {
            Form.api.bindevent($("#form"), function(){
                $.toast("修改成功");
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