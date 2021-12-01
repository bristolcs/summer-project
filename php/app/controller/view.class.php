<?php

class View extends Controller
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
     * mini-program whitelist interface
     * 
     * add one view to the article
     * 
     * article_id
     */
    function addViewForArticle($requestPayload)
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

        //insert
        $this->database_medoo->insert("views", [
            "wechat_user_id" => $this->this_wechat_user_id,
            "article_id" => $requestPayload['article_id'],
            "views_create_time" => date("Y-m-d-H-i-s")
        ]);
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * View a list of articles viewed by users
     */
    function getArticlelistByWechatuserView($requestPayload)
    {
        //get article list
        $list = $this->database_medoo->select("articles", [
            "[>]views" => ["article_id" => "article_id"]
        ], [
            "articles.article_id",
            "articles.article_title",
            "articles.article_img",
            "articles.article_content",
            "articles.author_user_id",
            "articles.article_create_time",
        ], [
            "views.wechat_user_id" => $this->this_wechat_user_id,
            "ORDER" => ["articles.article_create_time" => "DESC"]
        ]);

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
}
