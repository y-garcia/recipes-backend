<?php
/**
 * Author: Yeray García Quintana
 * Date: 11.11.2018
 */

namespace Recipes\db\entity;


use Recipes\config\Config;

class RecipeStep extends Entity
{
    protected $recipe_id;
    protected $description;
    protected $is_section;
    protected $sort_order;

    /**
     * RecipeStep constructor.
     * @param $id
     * @param $recipeId
     * @param $description
     * @param $isSection
     * @param $sortOrder
     */
    public function __construct($recipeId = null, $description = null, $isSection = null, $sortOrder = null, $id = null)
    {
        parent::__construct(Config::TABLE_RECIPE_STEP, $id);
        $this->recipe_id = $recipeId;
        $this->description = $description;
        $this->is_section = $isSection;
        $this->sort_order = $sortOrder;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return null
     */
    public function getIsSection()
    {
        return $this->is_section;
    }

    /**
     * @param null $is_section
     */
    public function setIsSection($is_section)
    {
        $this->is_section = $is_section;
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

}
