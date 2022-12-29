define(['fast','backend', 'template', 'moment', 'slimscroll', 'form','template','angular','cosmetic'], function (Fast,Backend, Template, Moment, undefined, Form, Template,angular, Cosmetic) {
    var Trade = {
        api: {
            assignEditView:function(op, row) {
                AngularApp.controller("edit", function($scope,$sce, $compile,$timeout) {
                    $scope.fields = fields;
                    $scope.row = row;
                    $scope.submit = function(data, ret){
                        Trade.api.close(data);
                    };
                    var html = Template("edit-tmpl",{state:op,'fields':"fields"});
                    $timeout(function(){
                        $("#data-view").html($compile(html)($scope));
                        $timeout(function(){Form.api.bindevent($("form[role=form]"), $scope.submit);});
                    });
                });
            }
        },
        init: function () {
        },

    };
    Trade.api = $.extend(Backend.api, Trade.api);
    window.Trade = Trade;
    Trade.init();
    return Trade;
});