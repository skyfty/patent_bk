let App = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.data.id = options.id;
    this.getDetail(options.id);
  },

  /**
   * 获取订单详情
   */
  getDetail: function (id) {
    let _this = this;
    App._get('principal/view', { id }, function (result) {
      _this.setData(result.data);
      wx.setStorage({//存储到本地
        key:"principal",
        data:result.data
      });
    });
  },

  /**
   * 跳转到商品详情
   */
  editAptitude: function (e) {
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../aptitude/edit?id=' + id
    });
  },

  /**
   * 取消订单
   */
  delPrincipal: function (e) {
    let _this = this;
    let id = _this.data.id;
    wx.showModal({
      title: "提示",
      content: "确认删除主体吗？",
      success: function (o) {
        if (o.confirm) {
          App._post_form('principal/del', { id }, function (result) {
            wx.navigateBack();
          });
        }
      }
    });
  },


  /**
   * 跳转到商品详情
   */
  editPrincipal: function (e) {
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../principal/edit?id=' + id
    });
  },

  /**
   * 发起付款
   */
  payOrder: function (e) {
    let _this = this;
    let order_id = _this.data.order_id;

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
          _this.getOrderDetail(order_id);
        },
        fail: function () {
          App.showError('订单未支付');
        },
      });
    });
  },

  /**
   * 确认收货
   */
  receipt: function (e) {
    let _this = this;
    let order_id = _this.data.order_id;
    wx.showModal({
      title: "提示",
      content: "确认收到商品？",
      success: function (o) {
        if (o.confirm) {
          App._post_form('user.order/receipt', { order_id }, function (result) {
            _this.getOrderDetail(order_id);
          });
        }
      }
    });
  },


});