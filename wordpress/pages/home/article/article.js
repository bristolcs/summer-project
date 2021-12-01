//Get application instance
const app = getApp()

Page({

  /**
   * Initial data of the page
   */
  data: {
    //Detailed list of articles
    article_detail: {
      article: {
        article_id: "",
        article_title: "",
        article_img: "",
        article_content: "",
        author_user_id: "",
        article_create_time: "",
        comments: 0,
        likes: 0,
        views: 0
      },
      comments: [],
      likes: [],
      comments_count: 0,
      likes_count: 0,
    },
    //Adjust the height of the operation bar
    InputBottom: 0,
    //Delete comment prompt box
    isShowDelTips: false,
    //Delete prompt
    show_tips_info: "",
    //Authorized login prompt
    isShowLogin: false
  },

  //Adjust the height of the operation bar
  InputFocus(e) {
    this.setData({
      InputBottom: e.detail.height
    })
  },
  InputBlur(e) {
    this.setData({
      InputBottom: 0
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
            that.refresh();
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

  fetchCommentsData() {
    var self = this;
    wx.showLoading({
      title: 'loading',
      mask: true
    });
    var getNewComments = wxRequest.getRequest(app.globalData.domain.getNewComments());
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
   * Get the details of the article
   */
  getArticleById() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=article&a=getArticleById&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          article_id: wx.getStorageSync('current_article_id')
        },
        success: res => {
          var result = res.data;
          if (result.status == 1) {
            that.setData({
              article_detail: result.data
            });
          } else {
            wx.showToast({
              title: result.message,
              icon: 'error',
              duration: 1500
            })
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
   * Add one view to the article
   */
  addViewForArticle() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=view&a=addViewForArticle&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          article_id: wx.getStorageSync('current_article_id')
        },
        success: res => {
          resolve(res);
        },
        fail: res => {
          reject(res);
        }
      })
    })

  },

  /**
   * Comment on the article
   */
  addCommentForArticle(e) {
    let that = this;
    //Check whether there is a token in the local
    if (!wx.getStorageSync('token') || wx.getStorageSync('token') == '') {
      //The authorization box pops up
      that.setData({
        isShowLogin: true,
      })
      return
    }
    //Judgment input is not empty
    if (e.detail.value.input.trim() == "") {
      //Prompt dialog box
      wx.showToast({
        title: 'illegal input',
        icon: 'error',
        duration: 1500
      })
      return
    }
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=comment&a=addCommentForArticle&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          article_id: wx.getStorageSync('current_article_id'),
          comment_content: e.detail.value.input.trim()
        },
        success: res => {
          wx.hideLoading();
          that.refresh();
          resolve(res);
        },
        fail: res => {
          reject(res);
        }
      })
    })
  },

  /**
   * Click comment response processing
   */
  handleDelComment(e) {
    let that = this;
    //get value
    wx.setStorageSync('current_comment_id', e.currentTarget.dataset.comment_id)
    //Judge whether the comment belongs to the user
    that.isCommentFromWechatuser().then(function (res) {
      var result = res.data;
      if (result.status == 1) {
        //If it belongs to user comments, call up the dialog box to confirm
        that.setData({
          isShowDelTips: true,
          show_tips_info: "Please confirm your operation"
        })
      }
    })
  },

  /**
   * Delete comments from articles
   */
  deleteCommentForArticle() {
    let that = this;
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    that.setData({
      isShowDelTips: false,
    })
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=comment&a=deleteCommentForArticle&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          comment_id: wx.getStorageSync('current_comment_id')
        },
        success: res => {
          wx.hideLoading();
          that.refresh();
          resolve(res);
        },
        fail: res => {
          reject(res);
        }
      })
    })
  },

  /**
   * Judge whether the comment belongs to the user
   */
  isCommentFromWechatuser() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=comment&a=isCommentFromWechatuser&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          comment_id: wx.getStorageSync('current_comment_id')
        },
        success: res => {
          resolve(res);
        },
        fail: res => {
          reject(res);
        }
      })
    })
  },

  /**
   * like
   */
  addLikeForArticle() {
    let that = this;
    //Check whether there is a token in the local
    if (!wx.getStorageSync('token') || wx.getStorageSync('token') == '') {
      //The authorization box pops up
      that.setData({
        isShowLogin: true,
      })
      return
    }
    //Start loading animation
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=like&a=addLikeForArticle&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          article_id: wx.getStorageSync('current_article_id')
        },
        success: res => {
          wx.hideLoading();
          var result = res.data;
          if (result.status == 1) {
            that.refresh();
          } else {
            that.delLikeForArticle();
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
   * Cancel the likes of articles
   */
  delLikeForArticle() {
    let that = this;
    //Start loading animation
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    return new Promise(function (resolve, reject) {
      wx.request({
        url: app.globalData.domain + '/api.php?c=like&a=delLikeForArticle&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          article_id: wx.getStorageSync('current_article_id')
        },
        success: res => {
          wx.hideLoading();
          var result = res.data;
          if (result.status == 1) {
            that.refresh();
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
   * Return to the main page
   */
  goHome() {
    wx.reLaunch({
      url: '/pages/home/index/index',
    })
  },

  /**
   * Hide prompt box
   */
  hideDelModal() {
    this.setData({
      isShowDelTips: false,
    })
  },

  /**
   * refresh
   */
  refresh() {
    let that = this;
    //Start loading animation
    wx.showLoading({
      title: 'Loading',
      mask: true,
    })
    //Get article content
    that.getArticleById().then(function (res) {
      //Stop Animation
      wx.hideLoading();
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
    wx.setStorageSync('current_article_id', options.article_id);
    //Get article content
    that.getArticleById().then(function (res) {
      //Add one view to the article
      that.addViewForArticle().then(function (res) {
        //Stop Animation
        wx.hideLoading();
      })
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
  onShow: function () {},

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