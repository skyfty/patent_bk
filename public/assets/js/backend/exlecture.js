define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic','bootstrap-treegrid',,'backend/prelecture'], function ($, Backend, Table, Form, Template,angular, Cosmetic,undefined,undefined, Prelecture) {
    var Controller = {
        lands:{
            index:function($scope, $compile,$timeout, data) {
            }
        },

        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "exlecture/index",dataType: 'json',
                    data:{
                        custom:{id:$scope.row.id}
                    },
                    success: function (data) {
                        if (data && data.rows && data.rows.length == 1) {
                            $scope.$apply(function(){
                                $parse("row").assign($scope, data.rows[0]);
                            });
                        }
                    }
                });
            };
        },
        scenery: {
            expound:function($scope, $compile,$timeout, data) {
                $scope.detailFormatter = function (index, row) {
                    var html = [];
                    html.push('<p><b>整句:</b> ' +  row.detail.join('') + '</p>');

                    return html.join('');
                };

                $scope.searchFieldsParams = function (param) {
                    param.custom = {
                        "lecture_model_id": $scope.row.id,
                    };
                    return param;
                };
                Table.api.init({
                    extend: {
                        index_url: 'expound/index',
                        edit_url: 'expound/edit',
                        del_url: 'expound/del',
                        table: 'expound',
                    },
                    commonSearch: false, //是否启用通用搜索
                    showExport: false,
                    search: false, //是否启用快速搜索
                });
                $scope.fields = data.fields;

                angular.element("#tab-" + $scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");

                $(".btn-add-expound").click(function(){
                    var url = '/expound/edit?exlecture_model_id=' + $scope.row.id;
                    var index = Backend.api.open(url, "新建流程", {
                        callback: function (res) {
                        }}
                    );
                    Layer.full(index);
                    return false;
                });
            },
        },
        api: {
        }
    };
    Controller.api = $.extend(Prelecture.api, Controller.api);
    return $.extend(Prelecture, Controller);
});