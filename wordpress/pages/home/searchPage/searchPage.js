//Get application instance
const app = getApp()

Page({

  /**
   * Initial data of the page
   */
  data: {
    //Article list
    article_list: [],
    //Search keywords
    article_search_keywords: "",
    //Search background
    website_search_img: app.globalData.domain + "/data/images/website-search.png"
  },

  /**
   * Get article list
   */
  getArticlelist() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=article&a=getArticlelist&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          article_search_keywords: that.data.article_search_keywords
        },
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              article_list: result.data
            });
          }
          resolve(res);
        },
        fail: res => {
          reject(res);
        }
      })
    })

  },

  /**
   * View Article Details
   */
  getArticleById(e) {
    wx.navigateTo({
      url: '/pages/home/article/article?article_id=' + e.currentTarget.dataset.article_id,
    })
  },

  /**
   * Life cycle function -- listening for page loading
   */
  onLoad: function (options) {
    let that = this;
    //Start loading animation
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    //get value
    that.setData({
      article_search_keywords: options.article_search_keywords
    })
    //Get article list
    that.getArticlelist().then(function (res) {
      //Stop Animation
      wx.hideLoading();
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})