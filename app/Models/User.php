<?php

namespace App\Models;

class User
{
    private $name = null;
    private $email = null;
    private $gender = null;
    private $avatar_url = null;


    public function __construct($name, $email, $gender, $avatar_url)
    {
        $this->name = $name;
        $this->email = $email;
        $this->gender = $gender;
        $this->avatar_url = $avatar_url;
    }

    public function toArray()
    {
        $array = [];
        foreach ($this as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }

    public static function fromGoogleUser($userinfo)
    {
        return new self($userinfo->name, $userinfo->email, $userinfo->gender, $userinfo->picture);
    }
}
