<?php

class Like extends Controller
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
     * like the article
     * 
     * article_id
     */
    function addLikeForArticle($requestPayload)
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

        //check is exist like record
        $result = $this->database_medoo->select("likes", ["like_id"], [
            "wechat_user_id" => $this->this_wechat_user_id,
            "article_id" => $requestPayload['article_id']
        ]);
        //prompt if there are records
        if (!empty($result)) {
            $data['status'] = 0;
            $data['message'] = 'i already liked it';
            return $data;
        }
        //insert record
        $this->database_medoo->insert("likes", [
            "wechat_user_id" => $this->this_wechat_user_id,
            "article_id" => $requestPayload['article_id'],
            "like_create_time" => date("Y-m-d-H-i-s")
        ]);
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * cancel the likes of articles
     * 
     * article_id
     */
    function delLikeForArticle($requestPayload)
    {
        $this->database_medoo->delete("likes", [
            "AND" => [
                "wechat_user_id" => $this->this_wechat_user_id,
                "article_id" => $requestPayload['article_id'],
            ]
        ]);
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * Get the likes list of articles
     * 
     * article_id
     */
    function getLikelistByArticleId($requestPayload)
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

        //get likes list
        $list = $this->database_medoo->select("likes",  [
            "[>]wechat_users" => ["wechat_user_id" => "wechat_user_id"]
        ], [
            "likes.like_id",
            "likes.wechat_user_id",
            "likes.like_create_time",
            "wechat_users.wechat_user_avatar",
            "wechat_users.wechat_user_nickname"
        ], [
            "article_id" => $requestPayload['article_id'],
            "ORDER" => ["like_create_time" => "DESC"]
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list;
        return $data;
    }

    /**
     * get the list of articles users like
     */
    function getArticlelistByWechatuserLike($requestPayload)
    {
        //get article list
        $list = $this->database_medoo->select("articles", [
            "[>]likes" => ["article_id" => "article_id"]
        ], [
            "articles.article_id",
            "articles.article_title",
            "articles.article_img",
            "articles.article_content",
            "articles.author_user_id",
            "articles.article_create_time",
        ], [
            "likes.wechat_user_id" => $this->this_wechat_user_id,
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
}
