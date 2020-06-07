<?php
/**
 * Author: Yeray García Quintana
 * Date: 11.11.2018
 */

namespace Recipes\db\entity;

use Recipes\config\Config;

class RecipeTag extends Entity
{
    protected $recipe_id;
    protected $tag_id;

    /**
     * RecipeTag constructor.
     * @param $id
     * @param $recipeId
     * @param $userId
     */
    public function __construct($recipeId = null, $userId = null, $id = null)
    {
        parent::__construct(Config::TABLE_RECIPE_TAG, $id);
        $this->recipe_id = $recipeId;
        $this->tag_id = $userId;
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
    public function getTagId()
    {
        return $this->tag_id;
    }

    /**
     * @param null $tag_id
     */
    public function setTagId($tag_id)
    {
        $this->tag_id = $tag_id;
    }

}
