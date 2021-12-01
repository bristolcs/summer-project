<?php

class Article extends Controller
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
     * add article
     * 
     * article_title
     * article_img
     * article_content
     */
    function addArticle($requestPayload)
    {
        $this->database_medoo->insert("articles", [
            "article_title" => $requestPayload['article_title'],
            "article_img" => $requestPayload['article_img'],
            "article_content" => $requestPayload['article_content'],
            "author_user_id" => $this->this_author_user_id,
            "article_create_time" => date("Y-m-d-H-i-s"),
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'sucess';
        return $data;
    }

    /**
     * delete article
     * 
     * article_id
     */
    function delArticleById($requestPayload)
    {
        $this->database_medoo->delete("articles", [
            "article_id" => $requestPayload['article_id']
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * modify the content, title and picture of the article
     * 
     * article_title
     * article_img
     * article_content
     */
    function editArticleById($requestPayload)
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

        $this->database_medoo->update("articles", [
            "article_title" => $requestPayload['article_title'],
            "article_img" => $requestPayload['article_img'],
            "article_content" => $requestPayload['article_content'],
        ], [
            "article_id" => $requestPayload['article_id']
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * Get the details of the article
     * including article content, comment list and like list
     * the comment list also includes the avatar and nickname of each comment user
     * the likes list includes the avatar and nickname of each commenter
     * 
     * article_id
     */
    function getArticleById($requestPayload)
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

        //get article content
        $list['article'] = $this->database_medoo->select("articles", [
            "article_title",
            "article_img",
            "article_content",
            "author_user_id",
            "article_create_time"
        ], [
            "article_id" => $requestPayload['article_id']
        ]);

        //handle
        foreach ($list['article'] as $key => $value) {
            //configure the picture name as a network direct link
            $list['article'][$key]['article_img'] = Config::$domain . '/data/images/' . $value['article_img'];
            //format time
            $timeStr = explode('-', $list['article'][$key]['article_create_time']);
            $list['article'][$key]['article_create_time'] = $timeStr[0] . '-' . $timeStr[1] . '-' . $timeStr[2];
        }

        //one record
        $list['article'] = $list['article'][0];

        //get the comment list of the article + the wechat user's avatar and nickname corresponding to each record
        $list['comments'] = $this->database_medoo->select("comments",  [
            "[>]wechat_users" => ["wechat_user_id" => "wechat_user_id"]
        ], [
            "comments.comment_id",
            "comments.comment_content",
            "comments.wechat_user_id",
            "comments.comment_create_time",
            "wechat_users.wechat_user_avatar",
            "wechat_users.wechat_user_nickname"
        ], [
            "comments.article_id" => $requestPayload['article_id'],
            "ORDER" => ["comments.comment_create_time" => "DESC"]
        ]);

        //format time
        foreach ($list['comments'] as $key => $value) {
            $timeStr = explode('-', $list['comments'][$key]['comment_create_time']);
            $list['comments'][$key]['comment_create_time'] = $timeStr[0] . '-' . $timeStr[1] . '-' . $timeStr[2];
        }

        //get the likes record of the article + the wechat user's Avatar and nickname corresponding to each record
        $list['likes'] = $this->database_medoo->select("likes",  [
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

        //format time
        foreach ($list['likes'] as $key => $value) {
            $timeStr = explode('-', $list['likes'][$key]['like_create_time']);
            $list['likes'][$key]['like_create_time'] = $timeStr[0] . '-' . $timeStr[1] . '-' . $timeStr[2];
        }

        //comments count
        $list['comments_count'] = sizeof($list['comments']);

        //likes count
        $list['likes_count'] = sizeof($list['likes']);

        //views count
        $list['views_count'] = $this->database_medoo->count('views', ["article_id" => $requestPayload['article_id']]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list;
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * get article list
     * each result includes article picture link, posting time, number of comments, number of likes and number of views
     * 
     * article_search_keywords
     */
    function getArticlelist($requestPayload)
    {
        $article_search_keywords = trim($requestPayload['article_search_keywords']);

        //processing keywords
        $article_search_keywords = "%$article_search_keywords%";

        //get article list
        $list = $this->database_medoo->select("articles",  [
            "article_id",
            "article_title",
            "article_img",
            "article_content",
            "author_user_id",
            "article_create_time",
        ], [
            "AND" => [
                "OR" => [
                    "article_title[~]" => $article_search_keywords,
                    "article_content[~]" => $article_search_keywords,
                ],
            ],
            "ORDER" => ["article_create_time" => "DESC"]
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
     * mini-program whitelist interface
     * 
     * article_search_keywords
     */
    function getRecommendationArticlelist($requestPayload)
    {
        //get article list
        $list = $this->database_medoo->rand("articles",  [
            "article_id",
            "article_title",
            "article_img",
            "article_content",
            "author_user_id",
            "article_create_time",
        ], [
            "LIMIT" => 3
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
}
