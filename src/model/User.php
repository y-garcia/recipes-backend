<?php
namespace Recipes\model;

class User
{
    private $id;
    private $username;
    private $passwordHash;
    private $uid;
    private $given_name;
    private $family_name;
    private $oauth;
    private $oauth_provider;

    /**
     * User constructor.
     * @param $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    static public function fromArray($array)
    {
        $newUser = new User($array["username"]);
        $newUser->setId($array["id"]);
        $newUser->setPasswordHash($array["password_hash"]);
        $newUser->setGivenName($array["given_name"]);
        $newUser->setFamilyName($array["family_name"]);
        $newUser->setOauthProvider($array["oauth_provider"]);
        $newUser->setUid($array["uid"]);
        return $newUser;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return bool|string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @param mixed $passwordHash
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
    }

    public function hashPassword($password)
    {
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getGivenName()
    {
        return $this->given_name;
    }

    /**
     * @param mixed $given_name
     */
    public function setGivenName($given_name)
    {
        $this->given_name = $given_name;
    }

    /**
     * @return mixed
     */
    public function getFamilyName()
    {
        return $this->family_name;
    }

    /**
     * @param mixed $family_name
     */
    public function setFamilyName($family_name)
    {
        $this->family_name = $family_name;
    }

    /**
     * @return mixed
     */
    public function getOauthProvider()
    {
        return $this->oauth_provider;
    }

    /**
     * @param mixed $oauth_provider
     */
    public function setOauthProvider($oauth_provider)
    {
        $this->oauth_provider = $oauth_provider;
    }

}