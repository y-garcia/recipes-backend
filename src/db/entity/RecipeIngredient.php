<?php
/**
 * Author: Yeray García Quintana
 * Date: 11.11.2018
 */

namespace Recipes\db\entity;


use Recipes\config\Config;

class RecipeIngredient extends Entity
{
    protected $recipe_id;
    protected $ingredient_id;
    protected $quantity;
    protected $unit_id;
    protected $sort_order;
    protected $created;
    protected $modified;

    /**
     * RecipeIngredient constructor.
     * @param $id
     * @param $recipe_id
     * @param $ingredient_id
     * @param $quantity
     * @param $unit_id
     * @param $sort_order
     * @param $created
     * @param $modified
     */
    public function __construct($recipe_id = null, $ingredient_id = null, $quantity = null, $unit_id = null, $sort_order = 0, $created = null, $modified = null, $id = null)
    {
        parent::__construct(Config::TABLE_RECIPE_INGREDIENT, $id);
        $this->recipe_id = $recipe_id;
        $this->ingredient_id = $ingredient_id;
        $this->quantity = $quantity;
        $this->unit_id = $unit_id;
        $this->sort_order = $sort_order;
        $this->created = $created;
        $this->modified = $modified;
    }

    /**
     * @return null
     */
    public function getRecipeId()
    {
        return $this->recipe_id;
    }

    /**
     * @param null $recipe_id
     */
    public function setRecipeId($recipe_id)
    {
        $this->recipe_id = $recipe_id;
    }

    /**
     * @return null
     */
    public function getIngredientId()
    {
        return $this->ingredient_id;
    }

    /**
     * @param null $ingredient_id
     */
    public function setIngredientId($ingredient_id)
    {
        $this->ingredient_id = $ingredient_id;
    }

    /**
     * @return null
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param null $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return null
     */
    public function getUnitId()
    {
        return $this->unit_id;
    }

    /**
     * @param null $unit_id
     */
    public function setUnitId($unit_id)
    {
        $this->unit_id = $unit_id;
    }

    /**
     * @return null
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param null $sort_order
     */
    public function setSortOrder($sort_order)
    {
        $this->sort_order = $sort_order;
    }

    /**
     * @return null
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param null $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return null
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param null $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

}
