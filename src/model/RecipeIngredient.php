<?php
/**
 * Author: Yeray García Quintana
 * Date: 31.07.2018
 */

namespace Recipes\model;

class RecipeIngredient
{
    private $id;
    private $recipe_id;
    private $ingredient_id;
    private $quantity;
    private $unit_id;
    private $sort_order;

    /**
     * Recipe constructor.
     * @param $id
     * @param $recipe_id
     * @param $ingredient_id
     * @param $quantity
     * @param $unit_id
     * @param $sort_order
     */
    public function __construct($id, $recipe_id, $ingredient_id, $quantity, $unit_id, $sort_order)
    {
        $this->id = $id;
        $this->recipe_id = $recipe_id;
        $this->ingredient_id = $ingredient_id;
        $this->quantity = $quantity;
        $this->unit_id = $unit_id;
        $this->sort_order = $sort_order;
    }

    static public function fromJson($obj)
    {
        return new RecipeIngredient(
            isset($obj->id) ? $obj->id : null,
            isset($obj->recipe_id) ? $obj->recipe_id : null,
            isset($obj->ingredient_id) ? $obj->ingredient_id : null,
            isset($obj->quantity) ? $obj->quantity : null,
            isset($obj->unit_id) ? $obj->unit_id : null,
            isset($obj->sort_order) ? $obj->sort_order : null
        );
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getRecipeId()
    {
        return $this->recipe_id;
    }

    /**
     * @return mixed
     */
    public function getIngredientId()
    {
        return $this->ingredient_id;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return mixed
     */
    public function getUnitId()
    {
        return $this->unit_id;
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

}
