//Get application instance
const app = getApp()

Page({

  /**
   * Initial data of the page
   */
  data: {
    //List of recommended articles on the home page
    recommendation_article_list: [],
    //List of recommended topics on home page
    recommendation_special_list: [],
    //Article list
    article_list: [],
    //Default display status of information prompt box
    isShowTips: false,
    //What the prompt box displays
    show_tips_info: ""
  },

  /**
   * Get the list of recommended topics on the home page
   */
  getRecommendationArticlelist() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=article&a=getRecommendationArticlelist&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {},
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              recommendation_article_list: result.data
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
   * Get the list of recommended topics on the home page
   */
  getRecommendationSpeciallist() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=special&a=getRecommendationSpeciallist&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {},
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              recommendation_special_list: result.data
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
   * Get a list of all articles
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
          article_search_keywords: ""
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
   * Search articles according to keywords
   */
  searchSubmit(e) {
    let that = this
    //Judgment input is not empty
    if (e.detail.value.input.trim() == "") {
      wx.showToast({
        title: 'illegal input',
        icon: 'error',
        duration: 1500
      })
      return
    }
    //Jump to a new page with parameters
    wx.navigateTo({
      url: '/pages/home/searchPage/searchPage?article_search_keywords=' + e.detail.value.input.trim(),
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
   * View the article list page under the topic
   */
  getArticlelistBySpecialId(e) {
    //Jump to a new page with parameters
    wx.navigateTo({
      url: '/pages/home/specialArticlePage/specialArticlePage?special_id=' + e.currentTarget.dataset.special_id,
    })
  },

  /**
   * Hide prompt box
   */
  hideModal() {
    this.setData({
      isShowTips: false,
    })
  },

  /**
   * Life cycle function -- listening for page loading
   */
  onLoad: function (options) {
    let that = this;
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    //Get a list of recommended articles
    that.getRecommendationArticlelist().then(function (res) {
      //Get a list of recommended special
      that.getRecommendationSpeciallist().then(function (res) {
        //Get article list
        that.getArticlelist().then(function (res) {
          wx.hideLoading();
        })
      })
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {},

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    let that = this;
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    //Get a list of recommended articles
    that.getRecommendationArticlelist().then(function (res) {
      //Get article list
      that.getArticlelist().then(function (res) {
        wx.hideLoading();
      })
    })
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