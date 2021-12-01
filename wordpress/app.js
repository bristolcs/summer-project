//app.js
App({
  onLaunch: function () {
    //Get system status bar information
    wx.getSystemInfo({
      success: e => {
        this.globalData.StatusBar = e.statusBarHeight;
        let capsule = wx.getMenuButtonBoundingClientRect();
        if (capsule) {
          this.globalData.Custom = capsule;
          this.globalData.CustomBar = capsule.bottom + capsule.top - e.statusBarHeight;
        } else {
          this.globalData.CustomBar = e.statusBarHeight + 50;
        }
      }
    })
  },
  globalData: {
    userInfo: null,
    openid: '',
    domain: "https://api2.wechat.witersen.com"
  },

  //Silent login to get token
  getTokenByCode: function () {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.login({
        success: res => {
          if (res.code) {
            wx.request({
              url: that.globalData.domain + '/api.php?c=wechat&a=getTokenByCode&t=mini',
              data: {
                code: res.code
              },
              header: {
                'content-type': 'application/json',
                'token': ''
              },
              method: 'POST',
              success: res => {
                var result = res.data;
                if (result.status == 1) {
                  //save token
                  wx.setStorageSync('token', result.data)
                }
                resolve(res);
              }
            })
          } else {
            reject('error');
          }
        }
      })
    })
  },

  //Store user information to the back end
  setWechatuserAvatarNickname() {
    let that = this;
    return new Promise(function (resolve, reject) {
      wx.request({
        url: that.globalData.domain + '/api.php?c=wechat&a=setWechatuserAvatarNickname&t=mini',
        method: "post",
        header: {
          'content-type': 'application/json',
          'token': wx.getStorageSync('token')
        },
        data: {
          wechat_user_avatar: that.globalData.userInfo.avatarUrl,
          wechat_user_nickname: that.globalData.userInfo.nickName
        },
        success: res => {
          resolve(res);
        },
        fail: res => {
          reject(res);
        }
      })
    })
  }
})