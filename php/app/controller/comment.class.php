<?php

class Comment extends Controller
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
     * Comment on the article
     * 
     * comment_content
     * article_id
     */
    function addCommentForArticle($requestPayload)
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

        $this->database_medoo->insert("comments", [
            "comment_content" => $requestPayload['comment_content'],
            "article_id" => $requestPayload['article_id'],
            "wechat_user_id" => $this->this_wechat_user_id,
            "comment_create_time" => date("Y-m-d-H-i-s")
        ]);

        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     *  delete comments from articles
     * 
     * comment_id
     */
    function deleteCommentForArticle($requestPayload)
    {
        // check comment is exist 
        $result = $this->database_medoo->select("comments", ["comment_id"], [
            "comment_id" => $requestPayload['comment_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'Comment does not exist';
            return $data;
        }

        //judge whether it is your own comment by the comment ID
        $result = $this->database_medoo->select("comments", ["wechat_user_id"], ["comment_id" => $requestPayload['comment_id']]);
        if ($result[0]['wechat_user_id'] != $this->this_wechat_user_id) {
            $data['status'] = 0;
            $data['message'] = 'You can only delete your own comments';
            return $data;
        }

        //delete 
        $result = $this->database_medoo->delete("comments", [
            "AND" => [
                "comment_id" => $requestPayload['comment_id']
            ]
        ]);

        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * Get a list of comments for an article
     * 
     * article_id
     */
    function getCommentlistByArticleId($requestPayload)
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

        //get comment list
        $list = $this->database_medoo->select("comments",  [
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

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list;
        return $data;
    }

    /**
     * Get a list of articles commented by users
     */
    function getArticlelistByWechatuserComment($requestPayload)
    {
        //get article list
        $list = $this->database_medoo->select("articles", [
            "[>]comments" => ["article_id" => "article_id"]
        ], [
            "articles.article_id",
            "articles.article_title",
            "articles.article_img",
            "articles.article_content",
            "articles.author_user_id",
            "articles.article_create_time",
        ], [
            "comments.wechat_user_id" => $this->this_wechat_user_id,
            "ORDER" => ["articles.article_create_time" => "DESC"]
        ]);

        //duplicate removal
        $list = array_values(array_unique($list, SORT_REGULAR));

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
     * Judge whether the comment belongs to the user
     * 
     * comment_id
     */
    function isCommentFromWechatuser($requestPayload)
    {
        // check coment is exist 
        $result = $this->database_medoo->select("comments", ["comment_id"], [
            "comment_id" => $requestPayload['comment_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'Comment does not exist';
            return $data;
        }
        //Judge whether it is your own comment by comment ID
        $result = $this->database_medoo->select("comments", ["wechat_user_id"], ["comment_id" => $requestPayload['comment_id']]);
        if ($result[0]['wechat_user_id'] != $this->this_wechat_user_id) {
            $data['status'] = 0;
            $data['message'] = 'Comment does not belong to user';
            return $data;
        } else {
            $data['status'] = 1;
            $data['message'] = 'Comments belong to users';
            return $data;
        }
    }
}
