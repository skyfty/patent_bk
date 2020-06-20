let App = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    substance_type: 'all',
    list: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.data.substance_type = options.type || 'all';
    this.setData({ substance_type: this.data.substance_type });
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    // 获取订单列表
    wx.removeStorage({key:"principal"});
    this.getList(this.data.substance_type);
  },

  /**
   * 获取订单列表
   */
  getList: function (substance_type) {
    let _this = this;
    App._get('principal/index', { substance_type }, function (result) {
      _this.setData(result.data);
      _this.setData({ substance_type: substance_type });

      result.data.list.length && wx.pageScrollTo({
        scrollTop: 0
      });
    });
  },

  /**
   * 切换标签
   */
  bindHeaderTap: function (e) {
    this.setData({ substance_type: e.target.dataset.type });
    // 获取订单列表
    this.getList(e.target.dataset.type);
  },

  /**
   * 取消订单
   */
  addPrincipal: function (e) {
    let _this = this;
    wx.navigateTo({
      url: '../principal/edit'
    });
  },

  /**
   * 确认收货
   */
  receipt: function (e) {
    let _this = this;
    let order_id = e.currentTarget.dataset.id;
    wx.showModal({
      title: "提示",
      content: "确认收到商品？",
      success: function (o) {
        if (o.confirm) {
          App._post_form('user.order/receipt', { order_id }, function (result) {
            _this.getList(_this.data.dataType);
          });
        }
      }
    });
  },

  /**
   * 发起付款
   */
  payOrder: function (e) {
    let _this = this;
    let order_id = e.currentTarget.dataset.id;

    // 显示loading
    wx.showLoading({ title: '正在处理...', });
    App._post_form('user.order/pay', { order_id }, function (result) {
      if (result.code === -10) {
        App.showError(result.msg);
        return false;
      }
      // 发起微信支付
      wx.requestPayment({
        timeStamp: result.data.timeStamp,
        nonceStr: result.data.nonceStr,
        package: 'prepay_id=' + result.data.prepay_id,
        signType: 'MD5',
        paySign: result.data.paySign,
        success: function (res) {
          // 跳转到已付款订单
          wx.navigateTo({
            url: '../order/detail?order_id=' + order_id
          });
        },
        fail: function () {
          App.showError('订单未支付');
        },
      });
    });
  },

  /**
   * 跳转订单详情页
   */
  detail: function (e) {
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../principal/detail?id=' + id
    });
  },

  onPullDownRefresh: function () {
    wx.stopPullDownRefresh();
  }


});