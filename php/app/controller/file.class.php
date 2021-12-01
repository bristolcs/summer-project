<?php

class File extends Controller
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
     * upload image
     */
    function uploadImg($requestPayload)
    {
        $base64 = $requestPayload['base64'];

        if (!isset($requestPayload["base64"]) || empty($requestPayload["base64"])) {
            $data['status'] = 0;
            $data['message'] = 'Incomplete parameters';
            return $data;
        }

        $filename = time() . mt_rand() . ".png";
        $filepath = Config::$savepath . '/data/images/' . $filename;

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            $base64_string = explode(',', $base64);
            file_put_contents($filepath, base64_decode($base64_string[1]));
        } else {
            file_put_contents($filepath, base64_decode($base64));
        }

        $data['status'] = 1;
        $data['message'] = 'success';
        $data['data']['filename'] = $filename;
        return $data;
    }

    /**
     *  delete image
     */
    function delImgById($requestPayload)
    {
        $filename = $requestPayload['filename'];

        if (!isset($requestPayload['filename']) || empty($requestPayload['filename'])) {
            $data['status'] = 0;
            $data['message'] = 'Incomplete parameters';
            return $data;
        }

        $filepath = Config::$savepath . '/data/images/' . $filename;
        @unlink($filepath);

        if (file_exists($filepath)) {
            $result['status'] = 0;
            $result['message'] = " delete fail";
            return $result;
        } else {
            $result['status'] = 1;
            $result['message'] = "success";
            return $result;
        }
    }
}
