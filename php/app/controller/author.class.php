<?php

class Author extends Controller
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
     * add author user
     * 
     * author_user_username
     * author_user_role
     * author_user_password
     */
    function addAuthorUser($requestPayload)
    {
        // check user is exist 
        $result = $this->database_medoo->select("author_users", ["author_user_id"], [
            "author_user_username" => $requestPayload['author_user_username'],
        ]);
        if (!empty($result)) {
            //return
            $data['status'] = 0;
            $data['message'] = 'user already exists';
            return $data;
        }

        //add user
        $this->database_medoo->insert("author_users", [
            "author_user_role" => $requestPayload['author_user_role'],
            "author_user_username" => $requestPayload['author_user_username'],
            "author_user_password" => md5($requestPayload['author_user_password']),
            "author_user_create_time" => date("Y-m-d-H-i-s"),
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * edit user
     * 
     * author_user_username
     * author_user_role
     * author_user_password
     * author_user_id
     */
    function editAuthorUser($requestPayload)
    {
        $this->database_medoo->update("author_users", [
            "author_user_role" => $requestPayload['author_user_role'],
            "author_user_username" => $requestPayload['author_user_username'],
            "author_user_password" => md5($requestPayload['author_user_password']),
        ], [
            "author_user_id" => $requestPayload['author_user_id']
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        return $data;
    }

    /**
     * get user list
     * 
     * user_search_keywords
     */
    function getAuthorlist($requestPayload)
    {
        $user_search_keywords = trim($requestPayload['user_search_keywords']);

        //processing keywords
        $user_search_keywords = "%$user_search_keywords%";

        //get user list
        $list = $this->database_medoo->select("author_users",  [
            "author_user_id",
            "author_user_role",
            "author_user_username",
            "author_user_create_time"
        ], [
            "AND" => [
                "OR" => [
                    "author_user_username[~]" => $user_search_keywords,
                ],
            ],
            "ORDER" => ["author_user_create_time" => "DESC"]
        ]);

        //return
        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data'] = $list;
        return $data;
    }

    /**
     * management system interface white list
     * 
     * author_user_username
     * author_user_password
     */
    function login($requestPayload)
    {
        $author_user_username = trim($requestPayload['author_user_username']);
        $author_user_password = trim($requestPayload['author_user_password']);

        if (empty($author_user_username) || empty($author_user_password)) {
            $data['status'] = 0;
            $data['message'] = 'login failed. The parameters are incomplete';
            return $data;
        }

        $result = $this->database_medoo->select('author_users',  [
            "author_user_id(userid)",
            "author_user_username(username)",
            "author_user_role(roleid)",
        ], [
            "author_user_username" => $author_user_username,
            "author_user_password" => md5($author_user_password)
        ]);

        if (empty($result)) {
            $data['status'] = 0;
            $data['message'] = 'Login failed. The user does not exist or the password is incorrect';
            return $data;
        }
        $token = parent::CreateToken($result[0]['userid']);

        //return
        $data['status'] = 1;
        $data['code'] = 200;
        $data['userid'] = $result[0]['userid'];
        $data['username'] = $result[0]['username'];
        $data['roleid'] = $result[0]['roleid'];
        $data['token'] = $token;
        $data['message'] = 'success';
        return $data;
    }
}
