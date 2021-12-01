<?php

class Classification extends Controller
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
     * add classfication 
     * 
     * classification_img
     * classification_title
     * classification_describe
     */
    function addClassification($requestPayload)
    {
        // check  classfication  is exist 
        $result = $this->database_medoo->select("classifications", ["classification_id"], [
            "classification_title" => $requestPayload['classification_title'],
        ]);
        if (!empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = ' classfication is exist';
            return $data;
        }

        $this->database_medoo->insert("classifications", [
            "classification_img" => $requestPayload['classification_img'],
            "classification_title" => $requestPayload['classification_title'],
            "classification_describe" => $requestPayload['classification_describe'],
            "classification_create_time" => date("Y-m-d-H-i-s"),
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     *  delete  classfication 
     * 
     * classification_id
     */
    function delClassification($requestPayload)
    {
        // check  classfication  is exist 
        $result = $this->database_medoo->select("classifications", ["classification_id"], [
            "classification_id" => $requestPayload['classification_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = ' classfication not exist';
            return $data;
        }

        $this->database_medoo->delete("classifications", [
            "classification_id" => $requestPayload['classification_id']
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * mini-program whitelist interface
     * 
     * get classfication list
     * 
     * classification_search_keywords
     */
    function getClassificationlist($requestPayload)
    {
        $classification_search_keywords = trim($requestPayload['classification_search_keywords']);

        //processing keywords
        $classification_search_keywords = "%$classification_search_keywords%";

        //get classfication list
        $list = $this->database_medoo->select("classifications",  [
            "classification_id",
            "classification_img",
            "classification_title",
            "classification_describe",
            "classification_create_time",
        ], [
            "AND" => [
                "OR" => [
                    "classification_title[~]" => $classification_search_keywords,
                    "classification_describe[~]" => $classification_search_keywords,
                ],
            ],
            "ORDER" => ["classification_create_time" => "DESC"]
        ]);

        //configure the picture name as a network direct link
        foreach ($list as $key => $value) {
            $list[$key]['classification_img'] = Config::$domain . '/data/images/' . $value['classification_img'];
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
     * get classfication article list
     * 
     * classification_id  classfication id
     */
    function getArticlelistByClassificationId($requestPayload)
    {
        //get article list
        $list = $this->database_medoo->select("articles", [
            "[>]article_classification" => ["article_id" => "article_id"]
        ], [
            "articles.article_id",
            "articles.article_title",
            "articles.article_img",
            "articles.article_content",
            "articles.author_user_id",
            "articles.article_create_time",
        ], [
            "article_classification.classification_id" => $requestPayload['classification_id'],
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
     * set classfication for article
     * 
     * article_id
     * classification_id 
     */
    function setClassificationForArticle($requestPayload)
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

        // check classfication  is exist 
        $result = $this->database_medoo->select("classifications", ["classification_id"], [
            "classification_id" => $requestPayload['classification_id'],
        ]);
        if (empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = ' classfication not exist';
            return $data;
        }

        // check whether the classification has been set for the article
        $result = $this->database_medoo->select("article_classification", ["article_classification_id"], [
            "article_id" => $requestPayload['article_id'],
            "classification_id" => $requestPayload['classification_id'],
        ]);
        if (!empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'The article already exists under the category';
            return $data;
        }

        //set classfication for article
        $this->database_medoo->insert("article_classification", [
            "article_id" => $requestPayload['article_id'],
            "classification_id" => $requestPayload['classification_id'],
            "article_classification_create_time" => date("Y-m-d-H-i-s"),
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * cancel classfication 
     * 
     * article_id
     * classification_id
     */
    function unsetClassificationForArticle($requestPayload)
    {
        $this->database_medoo->delete("article_classification", [
            "article_id" => $requestPayload['article_id'],
            "classification_id" => $requestPayload['classification_id'],
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }
}
