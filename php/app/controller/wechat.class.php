<?php

class Wechat extends Controller
{

    function __construct()
    {
        /*
         * avoid overriding the constructor of the parent class by the constructor of the child class
         */
        parent::__construct();
        /*
         * other custom operations
         */
    }

    /**
     * get token by code
     * 
     * code temporary voucher
     */
    function getTokenByCode($requestPayload)
    {
        $code = trim($requestPayload['code']);

        if (empty($code)) {
            $data['status'] = 0;
            $data['message'] = 'Incomplete parameters';
            return $data;
        }

        $requestUrl = "https://api.weixin.qq.com/sns/jscode2session?appid=" . Config::$appid . "&secret=" . Config::$secret . "&js_code=" . $code . "&grant_type=authorization_code";
        $result = curl_request($requestUrl);

        $result = json_decode($result, true);

        $session_key = $result['session_key'];
        $openid = $result['openid'];
        $token = parent::CreateToken($openid);

        $info = $this->database_medoo->select("wechat_users", ["wechat_user_id"], ["wechat_user_openid" => $openid]);
        if (empty($info)) {
            $this->database_medoo->insert("wechat_users", [
                "wechat_user_openid" => $openid,
                "wechat_user_session_key" => $session_key,
                "wechat_user_token" => $token,
                "wechat_user_create_time" => date("Y-m-d-H-i-s")
            ]);
        } else {
            $this->database_medoo->update("wechat_users", [
                "wechat_user_session_key" => $session_key,
                "wechat_user_token" => $token,
            ], [
                "wechat_user_openid" => $openid
            ]);
        }

        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $token;
        return $data;
    }

    /**
     * Save wechat user's nickname and avatar straight chain
     * 
     * wechat_user_avatar
     * wechat_user_nickname
     */
    function setWechatuserAvatarNickname($requestPayload)
    {
        $this->database_medoo->update("wechat_users", [
            "wechat_user_avatar" => $requestPayload['wechat_user_avatar'],
            "wechat_user_nickname" => $requestPayload['wechat_user_nickname'],
        ], [
            "wechat_user_id" => $this->this_wechat_user_id
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }
}
