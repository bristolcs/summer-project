<?php

/*
 * database info
 */
define("DB_TYPE", "mysql");
define("DB_HOST", "localhost");
define("DB_PORT", "3306");
define("DB_USER", "mini_wordpress");
define("DB_PASSWORD", "iGTZi533fNrJtXbH");
define("DB_NAME", "mini_wordpress");
define("DB_CHARSET", "utf8");

/*
 * Signature in token pattern of JWT
 */
define("SIGNATURE", "LJDFKSLDFKJSLDOIJFSDOIF9SUD8");

class Config
{
    /**
     * Request timeout setting
     * Unit: Second
     */
    public static $requestTimeout =  array(
        "curl_request" => 5, 
    );

    /**
     * domain
     */
    public static $domain = "https://api2.wechat.witersen.com";

    /**
     * uplaod file save path
     */
    public static $savepath = "/www/wwwroot/api2.wechat.witersen.com";

    /**
     * mini-program config
     */
    // public static $appid = "wxf5fa813c7d512427";
    // public static $secret = "5b42ff7092f7835bab2790e24333b941";

    public static $appid = "wxd4ce6d987d288df0";
    public static $secret = "e6b49b5b8c9f29eaac20d110f5ee9b82";

    /**
     * The applet interface does not need to verify the whitelist
     */
    public static $miniWhiteFunction = array(
        "getArticleById",
        "getArticlelist",
        "getClassificationlist",
        "getArticlelistByClassificationId",
        "getCommentlistByArticleId",
        "getSpeciallist",
        "getArticlelistBySpecialId",
        "getArticlelistByTagId",
        "getTaglistByArticleId",
        "addViewForArticle",
        "getLikelistByArticleId",
        "getRecommendationSpeciallist",
        "getSpecialById",
        "getRecommendationArticlelist",
        "getTokenByCode"
    );

    /**
     * The management system interface does not need authentication white list
     */
    public static $webWhiteFunction = array(
        "login",
    );
}
