<?php
/**
 * Author: Yeray García Quintana
 * Date: 11.11.2018
 */

namespace Recipes\db\entity;

use Recipes\config\Config;

class Recipe extends Entity
{
    /** @var string $name */
    protected $name;
    /** @var int $portions */
    protected $portions;
    /** @var int $duration */
    protected $duration;
    /** @var string $url */
    protected $url;
    protected $created;
    protected $modified;

    /**
     * Recipe constructor.
     * @param null $name
     * @param null $portions
     * @param null $duration
     * @param null $url
     * @param null $id
     */
    public function __construct($name = null, $portions = null, $duration = null, $url = null, $id = null)
    {
        parent::__construct(Config::TABLE_RECIPE, $id);
        $this->name = $name;
        $this->portions = $portions;
        $this->duration = $duration;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Recipe
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getPortions()
    {
        return $this->portions;
    }

    /**
     * @param int $portions
     * @return Recipe
     */
    public function setPortions($portions)
    {
        $this->portions = $portions;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     * @return Recipe
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Recipe
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param mixed $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

}
