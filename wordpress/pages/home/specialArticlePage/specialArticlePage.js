//Get application instance
const app = getApp()

Page({

  /**
   * Initial data of the page
   */
  data: {
    //Article list
    article_list: [],
    //special id
    special_id: 0,
    //specail info
    special: {
      special_img: "",
      special_title: "",
      special_describe: "",
      special_create_time: ""
    }
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
      special_id: options.special_id
    })
    //Obtain the topic information according to the topic ID
    that.getSpecialById().then(function (res) {
      //Get the article list according to the topic ID
      that.getArticlelistBySpecialId().then(function (res) {
        //Stop Animation
        wx.hideLoading();
      })
    })
  },

  /**
   * Get the list of articles under the topic
   */
  getArticlelistBySpecialId() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=special&a=getArticlelistBySpecialId&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          special_id: that.data.special_id
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
   * Get special information
   */
  getSpecialById() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=special&a=getSpecialById&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          special_id: that.data.special_id
        },
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              special: result.data
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
   * ??????????????????--??????????????????????????????
   */
  onReady: function () {

  },

  /**
   * ??????????????????--??????????????????
   */
  onShow: function () {
    let that = this;
    //Start loading animation
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    //Get the article list according to the topic ID
    that.getArticlelistBySpecialId().then(function (res) {
      //Stop Animation
      wx.hideLoading();
    })
  },

  /**
   * ??????????????????--??????????????????
   */
  onHide: function () {

  },

  /**
   * ??????????????????--??????????????????
   */
  onUnload: function () {

  },

  /**
   * ??????????????????????????????--????????????????????????
   */
  onPullDownRefresh: function () {

  },

  /**
   * ???????????????????????????????????????
   */
  onReachBottom: function () {

  },

  /**
   * ???????????????????????????
   */
  onShareAppMessage: function () {

  }
})