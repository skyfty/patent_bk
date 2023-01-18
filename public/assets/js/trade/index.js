define(['jquery', 'bootstrap', 'trade','form', 'table'], function ($, undefined, Trade, Form, Table) {
    var Controller = {
        index: function () {

        },
        login: function () {
            //让错误提示框居中
            Fast.config.toastr.positionClass = "toast-top-center";


            $("#login-form").data("validator-options", {
                invalid: function (form, errors) {
                    $.each(errors, function (i, j) {
                        Toastr.error(j);
                    });
                },
                target: '#errtips'
            });

            //为表单绑定事件
            Form.api.bindevent($("#login-form"), function (data) {
                localStorage.setItem("lastlogin", JSON.stringify({
                    id: data.id,
                    username: data.username,
                    avatar: data.avatar
                }));
                location.href = Backend.api.fixurl(data.url);
            }, function(data, ret){
                if (ret.msg === "验证码错误!") {
                    $("#login_captcha").trigger("click");
                }
            });

            $("#pd-form-username").on("change", function(){
                var username = $("#pd-form-username").val();
                if (username) {
                    $.ajax({
                        url:"/ajax/check",
                        data:{
                            model:"staff",name:"admin_name|telephone|idcode",value:username,
                        }
                    }).then(function(ret){
                        var branch_model_id = ret && ret.data && ret.data.branch_model_id? ret.data.branch_model_id:0;
                        $('[name="branch"]').val(branch_model_id);
                    });
                }
            });
        }
    };

    return Controller;
});