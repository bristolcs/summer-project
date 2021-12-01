<?php

class Tag extends Controller
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
     * add tag
     * 
     * tag_content
     */
    function addTag($requestPayload)
    {
        // check tag is exist 
        $result = $this->database_medoo->select("tags", ["tag_id"], [
            "tag_content" => $requestPayload['tag_content'],
        ]);
        if (!empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'tag is exist';
            return $data;
        }

        //add tag
        $this->database_medoo->insert("tags", [
            "tag_content" => $requestPayload['tag_content'],
            "tag_create_time" => date("Y-m-d-H-i-s"),
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     *  delete tag
     * 
     * tag_id
     */
    function delTag($requestPayload)
    {
        $this->database_medoo->delete("tags", [
            "tag_id" => $requestPayload['tag_id']
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * Get the tag list of articles
     * 
     * article_id
     */
    function getTaglistByArticleId($requestPayload)
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

        //Get the tag list of articles
        $list = $this->database_medoo->select("tags",  [
            "[>]article_tag" => ["tag_id" => "tag_id"]
        ], [
            "tags.tag_id",
            "tags.tag_content",
        ], [
            "article_tag.article_id" => $requestPayload['article_id']
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list;
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * get article list by tag
     * 
     * tag_id
     */
    function getArticlelistByTagId($requestPayload)
    {
        // check tag is exist 
        $result = $this->database_medoo->select("tags", ["tag_id"], [
            "tag_id" => $requestPayload['tag_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'tag does not exist';
            return $data;
        }

        //get article list
        $list = $this->database_medoo->select("articles", [
            "[>]article_tag" => ["article_id" => "article_id"]
        ], [
            "articles.article_id",
            "articles.article_title",
            "articles.article_img",
            "articles.article_content",
            "articles.author_user_id",
            "articles.article_create_time",
        ], [
            "article_tag.tag_id" => $requestPayload['tag_id'],
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
     * add tag for article
     * 
     * article_id
     * tag_id
     */
    function setTagForArticle($requestPayload)
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

        // check tag is exist 
        $result = $this->database_medoo->select("tags", ["tag_id"], [
            "tag_id" => $requestPayload['tag_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'tag does not exist';
            return $data;
        }

        // check if the tag has been added to the article
        $result = $this->database_medoo->select("article_tag", ["article_tag_id"], [
            "article_id" => $requestPayload['article_id'],
            "tag_id" => $requestPayload['tag_id'],
        ]);
        if (!empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'The article already contains the tag';
            return $data;
        }

        //add
        $this->database_medoo->insert("article_tag", [
            "article_id" => $requestPayload['article_id'],
            "tag_id" => $requestPayload['tag_id'],
            "article_tag_create_time" => date("Y-m-d-H-i-s"),
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     *  delete tags from articles
     * 
     * article_id
     * tag_id
     */
    function unsetTagForArticle($requestPayload)
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

        // check tag is exist 
        $result = $this->database_medoo->select("tags", ["tag_id"], [
            "tag_id" => $requestPayload['tag_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'tag does not exist';
            return $data;
        }

        $this->database_medoo->delete("article_tag", [
            "article_id" => $requestPayload['article_id'],
            "tag_id" => $requestPayload['tag_id'],
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }
}
