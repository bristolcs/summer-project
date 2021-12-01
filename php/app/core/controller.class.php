<?php

/*
 * Controller base class. All controllers should inherit this class
 */

//require model
require_once BASE_PATH . '/app/model/connModel.class.php';

//require controller
require_once BASE_PATH . '/app/controller/article.class.php';
require_once BASE_PATH . '/app/controller/author.class.php';
require_once BASE_PATH . '/app/controller/classification.class.php';
require_once BASE_PATH . '/app/controller/comment.class.php';
require_once BASE_PATH . '/app/controller/file.class.php';
require_once BASE_PATH . '/app/controller/like.class.php';
require_once BASE_PATH . '/app/controller/special.class.php';
require_once BASE_PATH . '/app/controller/tag.class.php';
require_once BASE_PATH . '/app/controller/view.class.php';
require_once BASE_PATH . '/app/controller/wechat.class.php';

//require function
require_once BASE_PATH . '/app/function/curl.function.php';

class Controller
{

    public $database_medoo;
    public $this_wechat_user_id;
    public $this_author_user_id;
    public $this_author_user_username;

    function __construct()
    {
        //database
        $this->database_medoo = (new connModel())->GetConn();

        //user info
        if (GlobalType == "mini") {
            $this->this_wechat_user_id = $this->getWechatuseridByToken();
        } else if (GlobalType == "web") {
            $this->this_author_user_id = $this->getAuthoruseridByToken();
            $this->this_author_user_username = $this->getAuthoruserusernameByToken();
        }
    }

    /**
     * prehandler check token
     */
    final function prehandler()
    {
        /**
         * mini-program and web interface 
         */
        if (GlobalType === "mini") {
            //No authentication required
            if (in_array(GlobalAction, Config::$miniWhiteFunction)) {
                return true;
            }
            //Authentication required
            $result = $this->database_medoo->select("wechat_users", ["wechat_user_id"], ["wechat_user_token" => GlobalToken]);
            if (empty($result)) {
                return false;
            }
        } else if (GlobalType == "web") {
            //No authentication required
            if (in_array(GlobalAction, Config::$webWhiteFunction)) {
                return true;
            }
            //Authentication required
            $data = $this->CheckToken(GlobalToken);
            if ($data['code'] != '200') {
                return false;
            }
        } else {
            //Unknown interface type authentication failed
            return false;
        }
        return true;
    }

    /**
     * check token
     */
    final function checkToken($token)
    {
        if (!isset($token) || empty($token)) {
            $data['code'] = '400';
            $data['message'] = 'bad request';
            return $data;
        }
        //check token
        $explode = explode('.', $token); //Take. Split token as array
        if (!empty($explode[0]) && !empty($explode[1]) && !empty($explode[2]) && !empty($explode[3])) {
            $info = $explode[0] . '.' . $explode[1] . '.' . $explode[2]; //info part
            $true_signature = hash_hmac('md5', $info, SIGNATURE); //right sig
            if (time() > $explode[2]) {
                $data['code'] = '401';
                $data['message'] = 'The token has expired. Please log in again';
                return $data;
            }
            if ($true_signature == $explode[3]) {
                $data['code'] = '200';
                $data['message'] = 'Token is legal';
                return $data;
            } else {
                $data['code'] = '400';
                $data['message'] = 'Illegal token';
                return $data;
            }
        } else {
            $data['code'] = '400';
            $data['message'] = 'Illegal token';
            return $data;
        }
    }

    /**
     * Generate a token for wechat users using openid
     * Or use userid to generate a token for the web user
     */
    final function CreateToken($openidOruserid)
    {
        $time = time();
        $end_time = time() + 86400;
        $info = $openidOruserid . '.' . $time . '.' . $end_time; //Set the token expiration time to one day
        //Generate a signature based on the above information (the key is siasqr)
        $signature = hash_hmac('md5', $info, SIGNATURE);
        //Finally, the two parts are spliced to get the final token string
        return $token = $info . '.' . $signature;
    }

    /**
     * Get wechat user's wechat according to the token_ user_ id
     */
    final function getWechatuseridByToken()
    {
        $result = $this->database_medoo->select("wechat_users", ["wechat_user_id"], ["wechat_user_token" => GlobalToken]);
        return empty($result) ? 0 : $result[0]["wechat_user_id"];
    }

    /**
     * Obtain the author of the web user according to the token_ user_ id
     */
    final function getAuthoruseridByToken()
    {
        $explode = explode('.', GlobalToken);
        return $explode[0];
    }

    /**
     * Obtain the author of the web user according to the token_ user_ username
     */
    final function getAuthoruserusernameByToken()
    {
        $explode = explode('.', GlobalToken);
        $result = $this->database_medoo->select("author_users", ["author_user_username"], ["author_user_id" => $explode[0]]);
        return empty($result) ? "" : $result[0]['author_user_username'];
    }
}
