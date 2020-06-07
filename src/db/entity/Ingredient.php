<?php
/**
 * Author: Yeray García Quintana
 * Date: 05.11.2018
 */

namespace Recipes\db\entity;

use Recipes\config\Config;

class Ingredient extends Entity
{
    protected $name;
    protected $aisle_id;
    protected $created;
    protected $modified;

    /**
     * Ingredient constructor.
     * @param $name
     * @param $aisleId
     * @param null $id
     */
    public function __construct($name = null, $aisleId = null, $id = null)
    {
        parent::__construct(Config::TABLE_INGREDIENT, $id);
        $this->name = $name;
        $this->aisle_id = $aisleId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAisleId()
    {
        return $this->aisle_id;
    }

    /**
     * @param mixed $aisle_id
     */
    public function setAisleId($aisle_id)
    {
        $this->aisle_id = $aisle_id;
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
