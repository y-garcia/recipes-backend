<?php
/**
 * Author: Yeray García Quintana
 * Date: 11.11.2018
 */

namespace Recipes\db\entity;

use Recipes\config\Config;

class Unit extends Entity
{
    protected $name_singular;
    protected $name_plural;

    /**
     * Unit constructor.
     * @param $id
     * @param $nameSingular
     * @param $namePlural
     */
    public function __construct($nameSingular = null, $namePlural = null, $id = null)
    {
        parent::__construct(Config::TABLE_UNIT, $id);
        $this->name_singular = $nameSingular;
        $this->name_plural = $namePlural;
    }

    /**
     * @return null
     */
    public function getNameSingular()
    {
        return $this->name_singular;
    }

    /**
     * @param null $name_singular
     */
    public function setNameSingular($name_singular)
    {
        $this->name_singular = $name_singular;
    }

    /**
     * @return null
     */
    public function getNamePlural()
    {
        return $this->name_plural;
    }

    /**
     * @param null $name_plural
     */
    public function setNamePlural($name_plural)
    {
        $this->name_plural = $name_plural;
    }

}
