define(['fast','backend', 'template', 'moment', 'slimscroll', 'form','template','angular','cosmetic'], function (Fast,Backend, Template, Moment, undefined, Form, Template,angular, Cosmetic) {
    var Trade = {
        api: {
            assignEditView:function(op, row, cb) {
                AngularApp.controller("edit", function($scope,$sce, $compile,$timeout) {
                    $scope.fields = fields;
                    $scope.row = row;
                    $scope.submit = function(data, ret){
                        if (typeof cb !== "function") {
                            Trade.api.close(data);
                        } else {
                            cb(data, ret);
                        }
                    };
                    var html = Template("edit-tmpl",{state:op,'fields':"fields"});
                    $timeout(function(){
                        $("#data-view").html($compile(html)($scope));
                        $timeout(function(){Form.api.bindevent($("form[role=form]"), $scope.submit);});
                    });
                });
            },

            formatModelKeyword:function(field, data, model) {
                var showData = [];
                if (field.content_list && field.content_list['sight']) {
                    $.each(field.content_list['sight'], function(k, v){
                        if (model[v]) {
                            showData.push(model[v]);
                        }
                    });
                } else {
                    $.each(model, function(k, v){
                        showData.push(v);
                    });
                }

                showData = showData.join(",");

                var control = data[field.name + "_type"] ||  data[field.name + "_model_type"] || field.defaultvalue;
                var url = control + "/index?ids=" + data[field.name + "_model_id"];
                url = Fast.api.fixurl(url);
                return '<a href="' + url + '" target="_blank" class="hinder" data-value="' + showData + '" title="' + showData + '">' + showData + '</a>';
            },
        },
        init: function () {
        },

    };
    Trade.api = $.extend(Cosmetic.api, Trade.api);
    window.Trade = Trade;
    Trade.init();
    return Trade;
});