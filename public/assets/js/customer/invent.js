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

                Form.api.bindevent($("#form"),  function(data, ret){
                    setTimeout(function(){window.location.replace(ret.url);}, 500);
                }, null, function () {
                    if ($("#treeview").size() > 0) {
                        var r = $("#treeview").jstree("get_all_checked");
                        $("input[name='row[rules]']").val(r.join(','));
                    }
                    return true;
                });


                $('#row_type').select({
                    title: "申报类型",
                    multi: true,
                    items: [
                        {
                            title: "发明",
                            value: "faming"
                        },
                        {
                            title: "实用新型",
                            value: "xinxing"
                        },
                        {
                            title: "专利合作条约（PCT）",
                            value: "pct"
                        }
                    ]
                }).on("change", function(){
                    var values = $(this).data("values");
                    $('[name="row[type]"]').val(values);
                })
            }
        }
    };
    Controller.api = $.extend(Customer.api, Controller.api);
    Controller.init();

    return Controller;
});
