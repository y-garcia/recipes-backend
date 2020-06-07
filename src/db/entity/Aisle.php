<?php
/**
 * Author: Yeray García Quintana
 * Date: 04.11.2018
 */

namespace Recipes\db\entity;

use Recipes\config\Config;

class Aisle extends Entity
{
    protected $name;

    /**
     * Aisle constructor.
     * @param $name
     * @param null $id
     */
    public function __construct($name = null, $id = null)
    {
        parent::__construct(Config::TABLE_AISLE, $id);
        $this->name = $name;
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
}
