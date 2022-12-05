define(['fast', 'template', 'moment'], function (Fast, Template, Moment) {
    var Trade = {
        api: {
        },
        init: function () {

        }
    };
    Trade.api = $.extend(Fast.api, Trade.api);
    //将Template渲染至全局,以便于在子框架中调用
    window.Template = Template;
    //将Moment渲染至全局,以便于在子框架中调用
    window.Moment = Moment;
    //将Backend渲染至全局,以便于在子框架中调用
    window.Trade = Trade;

    Trade.init();
    return Trade;
});