const app = getApp()

Page({

  /**
   * Initial data of the page
   */
  data: {
    //List of all articles
    special_list: [],
    //List 1 for rendering lists
    special_list_0: [],
    //List 2 for rendering purposes
    special_list_1: [],
    //Prompt box display status
    isShowTips: false,
    //The prompt box displays information
    show_tips_info: "",
    //Display status of authorization prompt box
    isShowLogin: false,
  },

  /**
   * Get topic list
   */
  getSpeciallist() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=special&a=getSpeciallist&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          special_search_keywords: ""
        },
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              special_list: result.data.special_list,
              special_list_0: result.data.special_list_0,
              special_list_1: result.data.special_list_1,
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
   * View the article list page under the topic
   */
  getArticlelistBySpecialId(e) {
    //Jump to a new page with parameters
    wx.navigateTo({
      url: '/pages/home/specialArticlePage/specialArticlePage?special_id=' + e.currentTarget.dataset.special_id,
    })
  },

  /**
   * Subscribe to topics
   */
  subscribeSpecial(e) {
    let that = this;
    //Check whether there is a token in the local
    if (!wx.getStorageSync('token') || wx.getStorageSync('token') == '') {
      //The authorization box pops up
      that.setData({
        isShowLogin: true,
      })
      return
    }
    wx.request({
      url: app.globalData.domain + '/api.php?c=special&a=subscribeSpecial&t=mini',
      method: "post",
      header: {
        'content-type': 'application/json',
        'token': wx.getStorageSync('token')
      },
      data: {
        special_id: e.currentTarget.dataset.special_id
      },
      success: res => {
        var result = res.data;
        if (result.status == 1) {
          that.onLoad()
        } else if (result.status == 401) {
          //Clear local token and userinfo
          wx.removeStorageSync('token', '');
          wx.removeStorageSync('userInfo', '');
          //The authorization box pops up
          that.setData({
            isShowLogin: true,
          })
        }

      }
    })
  },

  /**
   * Unsubscribe from a topic
   */
  desubscribeSpecial(e) {
    let that = this;
    //Check whether there is a token in the local
    if (!wx.getStorageSync('token') || wx.getStorageSync('token') == '') {
      //The authorization box pops up
      that.setData({
        isShowLogin: true,
      })
      return
    }
    wx.request({
      url: app.globalData.domain + '/api.php?c=special&a=desubscribeSpecial&t=mini',
      method: "post",
      header: {
        'content-type': 'application/json',
        'token': wx.getStorageSync('token')
      },
      data: {
        special_id: e.currentTarget.dataset.special_id
      },
      success: res => {
        var result = res.data;
        if (result.status == 1) {
          that.onLoad()
        } else if (result.status == 401) {
          //Clear local token
          wx.removeStorageSync('token', '');
          wx.removeStorageSync('userInfo', '');
          //The authorization box pops up
          that.setData({
            isShowLogin: true,
          })
        }

      }
    })
  },

  /**
   * Hide search prompt box
   */
  hideModal() {
    this.setData({
      isShowTips: false
    })
  },
  /**
   * Hide authorization prompt box
   */
  hideLoginModal() {
    this.setData({
      isShowLogin: false
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
    //First perform pop-up authorization and call wx.getuserprofile
    wx.getUserProfile({
      desc: 'For user login',
      success: (res) => {
        //Save to global variable
        app.globalData.userInfo = res.userInfo
        //save to storage
        wx.setStorageSync('userInfo', res.userInfo)
        //Perform a silent login with the user's consent
        app.getTokenByCode().then(function (res) {
          //Store user information in the back end
          app.setWechatuserAvatarNickname().then(function (res) {
            //Success prompt
            wx.showToast({
              title: 'success',
              icon: 'success',
              duration: 1500
            })
            //Reload
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
   * Life cycle function -- listening for page loading
   */
  onLoad: function (options) {
    let that = this;
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    //Get topic list
    that.getSpeciallist().then(function (res) {
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