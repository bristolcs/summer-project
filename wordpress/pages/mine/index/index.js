//Get application instance
const app = getApp()

Page({

  /**
   * Initial data of the page
   */
  data: {
    // CustomBar: app.globalData.CustomBar,
    TabCur: 0,
    tabNav: ['view', 'comment', 'like', 'encourage', 'subscribe'],
    //Display status of authorization prompt box
    isShowLogin: false,
    //User information
    userInfo: [],
    //Browse article records
    view_article_list: [],
    //Review article record
    com_article_list: [],
    //Like article record
    like_article_list: [],
    //Subscribe article record
    sub_article_list: [],
  },

  /**
   * View user browsed list of recommended topics on home page
   */
  getArticlelistByWechatuserView() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=view&a=getArticlelistByWechatuserView&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
        },
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              view_article_list: result.data
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
   * View user liked list of recommended topics on home page
   */
  getArticlelistByWechatuserLike() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=like&a=getArticlelistByWechatuserLike&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
        },
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              like_article_list: result.data
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
   * View user commeted list of recommended topics on home page
   */
  getArticlelistByWechatuserComment() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=comment&a=getArticlelistByWechatuserComment&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
        },
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              com_article_list: result.data
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
   * Get user subscription topics list of recommended topics on home page
   */
  getArticlelistByWechatuserSubscribe() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=special&a=getArticlelistByWechatuserSubscribe&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
        },
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              sub_article_list: result.data
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
   * Authorized login
   */
  loginNow() {
    let that = this;
    //Hide previous prompt box
    that.setData({
      isShowLogin: false
    })
    wx.showLoading({
      title: 'Loading',
    })
    setTimeout(function () {
      wx.hideLoading();
    }, 800)
    //First perform pop-up authorization and call wx.getuserprofile
    wx.getUserProfile({
      desc: 'For user login',
      success: (res) => {
        //Save to global variable
        app.globalData.userInfo = res.userInfo
        //Save to storage
        wx.setStorageSync('userInfo', res.userInfo)
        //Perform a silent login with the user's consent
        app.getTokenByCode().then(function (res) {
          //Store user information in the back end
          app.setWechatuserAvatarNickname().then(function (res) {
            wx.hideLoading();
            //Success prompt
            wx.showToast({
              title: 'success',
              icon: 'success',
              duration: 1500
            })
            that.onLoad();
          })
        })
      },
      fail: (res) => {
        //Failure prompt
        wx.showToast({
          title: 'fail',
          icon: 'error',
          duration: 1500
        })
      }
    })
  },

  /**
   * logout
   */
  loginOut() {
    let that = this;
    //Clear local token and userinfo
    wx.removeStorageSync('token', '');
    wx.removeStorageSync('userInfo', '');
    //clear vallue
    that.userInfo = [];
    //Delay animation for smooth logout
    wx.showLoading({
      title: 'Loading',
    })
    setTimeout(function () {
      wx.hideLoading()
      //Timed jump
      wx.reLaunch({
        url: '/pages/home/index/index'
      })
    }, 800)
  },

  /**
   * Hide authorization prompt box
   */
  hideLoginModal() {
    this.setData({
      isShowLogin: false
    })
  },

  //Navigation switching
  tabSelect(e) {
    let that = this;
    //Check whether there is a token in the local
    if (!wx.getStorageSync('token') || wx.getStorageSync('token') == '') {
      //The authorization box pops up
      that.setData({
        isShowLogin: true,
      })
      return
    }
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    that.setData({
      TabCur: e.currentTarget.dataset.id,
    })
    if (that.data.TabCur == 0) {
      //view
      that.getArticlelistByWechatuserView().then(function (res) {
        wx.hideLoading();
      })
    } else if (that.data.TabCur == 1) {
      //comment
      that.getArticlelistByWechatuserComment().then(function (res) {
        wx.hideLoading();
      })
    } else if (that.data.TabCur == 2) {
      //like
      that.getArticlelistByWechatuserLike().then(function (res) {
        wx.hideLoading();
      })
    } else if (that.data.TabCur == 3) {
      wx.hideLoading();
      //encourage
    } else if (that.data.TabCur == 4) {
      //subscribe
      that.getArticlelistByWechatuserSubscribe().then(function (res) {
        wx.hideLoading();
      })
    }

  },

  /**
   * Life cycle function -- listening for page loading
   */
  onLoad: function (options) {
    let that = this;
    //Check whether there is a token in the local
    if (!wx.getStorageSync('token') || wx.getStorageSync('token') == '') {
      //The authorization box pops up
      that.setData({
        isShowLogin: true,
      })
      return
    }
    //hava token
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    //Read personal avatar and nickname information
    that.setData({
      userInfo: wx.getStorageSync('userInfo')
    })
    //view
    that.getArticlelistByWechatuserView().then(function (res) {
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
    this.onLoad()
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