<?php

namespace app\common\library;

class Aip
{
    protected static $instance = null;

    static protected $APP_ID = '15853994';
    static protected $API_KEY = 'g7GRDQXofbD8r0AP6caQ6X5S';
    static protected $SECRET_KEY = '177ICfyn2Y7QmDqnBqj5gX5hEbxS7dnm';


    public function __construct($options = [])
    {
        $this->options = array_merge($this->config, $options);
    }

    public static function instance($options = [])
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    static public function detect($image,$imageType = "BASE64") {
        require_once EXTEND_PATH  . '/aip/AipFace.php';
        $client = new \AipFace(Aip::$APP_ID, Aip::$API_KEY, Aip::$SECRET_KEY);
        $options = array();
        $options["face_field"] = "age";
        $options["max_face_num"] = 1;
        $options["face_type"] = "LIVE";
        return $client->detect($image, $imageType, $options);
    }

    static public function addUser($facetoken, $uid, $group="customer") {
        require_once EXTEND_PATH  . '/aip/AipFace.php';
        $client = new \AipFace(Aip::$APP_ID, Aip::$API_KEY, Aip::$SECRET_KEY);
        $client->faceDelete($uid,  $group, $facetoken);
        $options = array();
        return $client->addUser($facetoken, "FACE_TOKEN", $group, $uid, $options);
    }

    static public function updateUser($facetoken, $uid, $group="customer") {
        require_once EXTEND_PATH  . '/aip/AipFace.php';
        $client = new \AipFace(Aip::$APP_ID, Aip::$API_KEY, Aip::$SECRET_KEY);
        $options = array();
        return $client->updateUser($facetoken, "FACE_TOKEN", $group, $uid, $options);
    }

    static public function faceDelete($facetoken,$uid, $group="customer") {
        require_once EXTEND_PATH  . '/aip/AipFace.php';
        $client = new \AipFace(Aip::$APP_ID, Aip::$API_KEY, Aip::$SECRET_KEY);
        return $client->faceDelete($uid,  $group, $facetoken);
    }

    static public function search($image, $group="customer") {
        require_once EXTEND_PATH  . '/aip/AipFace.php';
        $client = new \AipFace(Aip::$APP_ID, Aip::$API_KEY, Aip::$SECRET_KEY);
        $options = array();
        $options["max_user_num"] = 1;
        return $client->search($image, "BASE64", $group, $options);
    }

    static public function groupAdd($group) {
        require_once EXTEND_PATH  . '/aip/AipFace.php';
        $client = new \AipFace(Aip::$APP_ID, Aip::$API_KEY, Aip::$SECRET_KEY);
        $options = array();
        return $client->groupAdd($group,$options);
    }

}
