<?php

class Special extends Controller
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
     * add special
     * 
     * special_title
     * special_img
     * special_describe
     */
    function addSpecial($requestPayload)
    {
        // check special is exist 
        $result = $this->database_medoo->select("specials", ["special_id"], [
            "special_title" => $requestPayload['special_title'],
        ]);
        if (!empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'special already exists';
            return $data;
        }

        //add special
        $this->database_medoo->insert("specials", [
            "special_img" => $requestPayload['special_img'],
            "special_title" => $requestPayload['special_title'],
            "special_describe" => $requestPayload['special_describe'],
            "special_create_time" => date("Y-m-d-H-i-s"),
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     *  delete special
     * 
     * special_id
     */
    function delSpecial($requestPayload)
    {
        $this->database_medoo->delete("specials", [
            "special_id" => $requestPayload['special_id']
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * special_id
     */
    function getSpecialById($requestPayload)
    {
        // check special is exist 
        $result = $this->database_medoo->select("specials", ["special_id"], [
            "special_id" => $requestPayload['special_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'special already exists';
            return $data;
        }

        //get special info
        $list['special'] = $this->database_medoo->select("specials", [
            "special_img",
            "special_title",
            "special_describe",
            "special_create_time"
        ], [
            "special_id" => $requestPayload['special_id']
        ]);

        //configure the picture name as a network direct link
        foreach ($list['special'] as $key => $value) {
            $list['special'][$key]['special_img'] = Config::$domain . '/data/images/' . $value['special_img'];
        }

        //one record
        $list['special'] = $list['special'][0];

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list['special'];
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * special_search_keywords
     */
    function getSpeciallist($requestPayload)
    {
        $special_search_keywords = trim($requestPayload['special_search_keywords']);

        //processing keywords
        $special_search_keywords = "%$special_search_keywords%";

        //get special list
        $special_list = $this->database_medoo->select("specials",  [
            "special_id",
            "special_img",
            "special_title",
            "special_describe",
            "special_create_time",
        ], [
            "AND" => [
                "OR" => [
                    "special_title[~]" => $special_search_keywords,
                    "special_describe[~]" => $special_search_keywords,
                ],
            ],
            "ORDER" => ["special_create_time" => "DESC"]
        ]);

        //get the list of topics subscribed by current wechat users
        $subList = $this->database_medoo->select("subscribe_special", "special_id", [
            "wechat_user_id" => $this->this_wechat_user_id
        ]);

        //handle
        foreach ($special_list as $key => $value) {
            //configure the picture name as a network direct link
            $special_list[$key]['special_img'] = Config::$domain . '/data/images/' . $value['special_img'];
            //determine whether to subscribe
            $special_list[$key]['isSubscribe'] = 0;
            if (in_array($value['special_id'], $subList)) {
                $special_list[$key]['isSubscribe'] = 1;
            }
        }

        //explode
        $split_list = array_chunk($special_list, round(sizeof($special_list) / 2));

        $result = array(
            'special_list' => $special_list,
            'special_list_0' => $split_list[0],
            'special_list_1' => $split_list[1],
        );

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $result;
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * special_search_keywords
     */
    function getRecommendationSpeciallist($requestPayload)
    {
        //get spcial list
        $list = $this->database_medoo->rand("specials",  [
            "special_id",
            "special_img",
            "special_title",
            "special_describe",
            "special_create_time",
        ], [
            "LIMIT" => 3
        ]);

        $subList = $this->database_medoo->select("subscribe_special", "special_id", [
            "wechat_user_id" => $this->this_wechat_user_id
        ]);

        //handle
        foreach ($list as $key => $value) {
            //configure the picture name as a network direct link
            $list[$key]['special_img'] = Config::$domain . '/data/images/' . $value['special_img'];
            $list[$key]['isSubscribe'] = 0;
            if (in_array($value['special_id'], $subList)) {
                $list[$key]['isSubscribe'] = 1;
            }
        }

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list;
        return $data;
    }

    /**
     * user subscribe special
     * 
     * special_id
     */
    function subscribeSpecial($requestPayload)
    {
        // check special is exist 
        $result = $this->database_medoo->select("specials", ["special_id"], [
            "special_id" => $requestPayload['special_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'special already exists';
            return $data;
        }

        // check special is subscribe
        $result = $this->database_medoo->select("subscribe_special", ["subscribe_special_id"], [
            "wechat_user_id" => $this->this_wechat_user_id,
            "special_id" => $requestPayload['special_id'],
        ]);
        if (!empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'you have subscribed to a topic';
            return $data;
        }

        //subscribe to topics
        $this->database_medoo->insert("subscribe_special", [
            "wechat_user_id" => $this->this_wechat_user_id,
            "special_id" => $requestPayload['special_id'],
            "subscribe_special_create_time" => date("Y-m-d-H-i-s"),
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * user unsubscribes from the topic
     * 
     * special_id
     */
    function desubscribeSpecial($requestPayload)
    {
        $this->database_medoo->delete("subscribe_special", [
            "wechat_user_id" => $this->this_wechat_user_id,
            "special_id" => $requestPayload['special_id'],
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * Get the list of articles under the topic
     * 
     * special_id
     */
    function getArticlelistBySpecialId($requestPayload)
    {
        // check special is exist 
        $result = $this->database_medoo->select("specials", ["special_id"], [
            "special_id" => $requestPayload['special_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'special already exists';
            return $data;
        }

        //get article list
        $list = $this->database_medoo->select("articles", [
            "[>]article_special" => ["article_id" => "article_id"]
        ], [
            "articles.article_id",
            "articles.article_title",
            "articles.article_img",
            "articles.article_content",
            "articles.author_user_id",
            "articles.article_create_time",
        ], [
            "article_special.special_id" => $requestPayload['special_id'],
            "ORDER" => ["articles.article_create_time" => "DESC"]
        ]);

        // calculate the number of comments, likes, views
        foreach ($list as $key => $value) {
            //comments count
            $list[$key]['comments'] = $this->database_medoo->count('comments', ["article_id" => $value['article_id']]);
            //likes count
            $list[$key]['likes'] = $this->database_medoo->count('likes', ["article_id" => $value['article_id']]);
            //views count
            $list[$key]['views'] = $this->database_medoo->count('views', ["article_id" => $value['article_id']]);
            //configure the picture name as a network direct link
            $list[$key]['article_img'] = Config::$domain . '/data/images/' . $value['article_img'];
            //format time
            $timeStr = explode('-', $list[$key]['article_create_time']);
            $list[$key]['article_create_time'] = $timeStr[0] . '-' . $timeStr[1] . '-' . $timeStr[2];
        }

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list;
        return $data;
    }

    /**
     * set a topic for the article
     * 
     * article_id
     * special_id
     */
    function setSpecialForArticle($requestPayload)
    {
        // check article is exist 
        $result = $this->database_medoo->select("articles", ["article_id"], [
            "article_id" => $requestPayload['article_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'article does not exist';
            return $data;
        }

        // check special is exist 
        $result = $this->database_medoo->select("specials", ["special_id"], [
            "special_id" => $requestPayload['special_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'special already exists';
            return $data;
        }

        // check if the article has been themed
        $result = $this->database_medoo->select("article_special", ["article_special_id"], [
            "article_id" => $requestPayload['article_id'],
            "special_id" => $requestPayload['special_id'],
        ]);
        if (!empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'the article already exists under the topic';
            return $data;
        }

        //add  classfication form article
        $this->database_medoo->insert("article_special", [
            "article_id" => $requestPayload['article_id'],
            "special_id" => $requestPayload['special_id'],
            "article_special_create_time" => date("Y-m-d-H-i-s"),
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * Cancel the special of the article
     * 
     * article_id
     * special_id
     */
    function unsetSpecialForArticle($requestPayload)
    {
        $this->database_medoo->delete("article_special", [
            "article_id" => $requestPayload['article_id'],
            "special_id" => $requestPayload['special_id'],
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * get the list of all articles that users subscribe to the topic
     */
    function getArticlelistByWechatuserSubscribe($requestPayload)
    {
        //get article list
        $list = $this->database_medoo->select("articles", [
            "[>]article_special" => ["article_id" => "article_id"],
            "[>]subscribe_special" => ["article_special.special_id" => "special_id"]
        ], [
            "articles.article_id",
            "articles.article_title",
            "articles.article_img",
            "articles.article_content",
            "articles.author_user_id",
            "articles.article_create_time",
        ], [
            "subscribe_special.wechat_user_id" => $this->this_wechat_user_id,
            "ORDER" => ["articles.article_create_time" => "DESC"]
        ]);

        // calculate the number of comments, likes, views
        foreach ($list as $key => $value) {
            //comments count
            $list[$key]['comments'] = $this->database_medoo->count('comments', ["article_id" => $value['article_id']]);
            //likes count
            $list[$key]['likes'] = $this->database_medoo->count('likes', ["article_id" => $value['article_id']]);
            //views count
            $list[$key]['views'] = $this->database_medoo->count('views', ["article_id" => $value['article_id']]);
            //configure the picture name as a network direct link
            $list[$key]['article_img'] = Config::$domain . '/data/images/' . $value['article_img'];
        }

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list;
        return $data;
    }

    /**
     * get all lists of topics subscribed by users
     */
    function getSubscribespeciallist($requestPayload)
    {
        //get special list
        $list = $this->database_medoo->select("specials", [
            "[>]subscribe_special" => ["special_id" => "special_id"]
        ], [
            "specials.special_id",
            "specials.special_img",
            "specials.special_title",
            "specials.special_describe",
            "specials.special_create_time",
        ], [
            "subscribe_special.wechat_user_id" => $this->this_wechat_user_id,
            "ORDER" => ["specials.special_create_time" => "DESC"]
        ]);

        //configure the picture name as a network direct link
        foreach ($list as $key => $value) {
            $list[$key]['special_img'] = Config::$domain . '/data/images/' . $value['special_img'];
        }

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list;
        return $data;
    }
}
