let App = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    substance_type: 'all',
    list: [],
    isShow:false
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
    this.setData({
      isShow: false,
    });
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
    let principalclass = e.currentTarget.dataset.principalclass;
    let url = '../principal/company/edit?principalclass='+ principalclass;
    if (principalclass ==1) {
      url = '../principal/persion/edit?principalclass='+ principalclass;
    }
    wx.navigateTo({url: url});
  },
  
  /**
   * 跳转订单详情页
   */
  detail: function (e) {
    let id = e.currentTarget.dataset.id;
    let principalclass = e.currentTarget.dataset.principalclass;
    let url = '../principal/company/detail?id='+ id;
    if (principalclass ==1) {
      url = '../principal/persion/detail?id='+ id;
    }
    wx.navigateTo({url: url});
  },

  onPullDownRefresh: function () {
    wx.stopPullDownRefresh();
  },

   /**
     * 导航菜单切换事件
     */
  _onToggleShow: function(e) {
    this.setData({
      isShow: !this.data.isShow,
    });
  },
});