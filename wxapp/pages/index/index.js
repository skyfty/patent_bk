let App = getApp();

Page({
  data: {
    indicatorDots: true, // 是否显示面板指示点	
    interval: 3000, // 自动切换时间间隔
  },

  onLoad: function() {
    let _this = this;
    this.getIndexData();
  },

  /**
   * 获取首页数据
   */
  getIndexData: function() {
    let _this = this;
    // App._get('index/page', {}, function(result) {
    //   _this.setData(result.data);
    // });
  },

  onShareAppMessage: function() {
    return {
      title: "小程序首页",
      desc: "",
      path: "/pages/index/index"
    };
  }
});