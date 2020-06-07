<?php
/**
 * Author: Yeray García Quintana
 * Date: 27.08.2018
 */

namespace Recipes\model;

class RecipeStep
{
    private $id;
    private $recipe_id;
    private $description;
    private $is_section;
    private $sort_order;

    /**
     * Recipe constructor.
     * @param $id
     * @param $recipe_id
     * @param $description
     * @param $is_section
     * @param $sort_order
     */
    public function __construct($id, $recipe_id, $description, $is_section, $sort_order)
    {
        $this->id = $id;
        $this->recipe_id = $recipe_id;
        $this->description = $description;
        $this->is_section = $is_section;
        $this->sort_order = $sort_order;
    }

    static public function fromJson($obj)
    {
        return new RecipeStep(
            isset($obj->id) ? $obj->id : null,
            isset($obj->recipe_id) ? $obj->recipe_id : null,
            isset($obj->description) ? $obj->description : null,
            isset($obj->is_section) ? $obj->is_section ? "1" : "0" : null,
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getIsSection()
    {
        return $this->is_section;
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

}
