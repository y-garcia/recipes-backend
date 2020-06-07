<?php
/**
 * Author: Yeray García Quintana
 * Date: 11.11.2018
 */

namespace Recipes\db\entity;

use Recipes\config\Config;

class Tag extends Entity
{
    protected $name;

    /**
     * Tag constructor.
     * @param $name
     * @param null $id
     */
    public function __construct($name = null, $id = null)
    {
        parent::__construct(Config::TABLE_TAG, $id);
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
