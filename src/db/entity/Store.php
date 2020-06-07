<?php
/**
 * Author: Yeray García Quintana
 * Date: 11.11.2018
 */

namespace Recipes\db\entity;

use Recipes\config\Config;

class Store extends Entity
{
    protected $name;

    /**
     * Store constructor.
     * @param $name
     * @param null $id
     */
    public function __construct($name = null, $id = null)
    {
        parent::__construct(Config::TABLE_RECIPE_STEP, $id);
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
