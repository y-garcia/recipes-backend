<?php

namespace Recipes\db\entity;

use Recipes\config\Config;

class User extends Entity
{
    private $username;
    private $passwordHash;
    private $given_name;
    private $family_name;

    /**
     * User constructor.
     * @param $id
     * @param $username
     * @param $passwordHash
     * @param $given_name
     * @param $family_name
     */
    public function __construct($username = null, $passwordHash = null, $given_name = null, $family_name = null, $id = null)
    {
        parent::__construct(Config::TABLE_USER, $id);
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->given_name = $given_name;
        $this->family_name = $family_name;
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
}