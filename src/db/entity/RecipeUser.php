<?php
/**
 * Author: Yeray García Quintana
 * Date: 10.12.2018
 */

namespace Recipes\db\entity;

use Ramsey\Uuid\Uuid;
use Recipes\config\Config;

class RecipeUser extends Entity
{
    /** @var Uuid $recipe_id */
    protected $recipe_id;
    /** @var Uuid $user_id */
    protected $user_id;

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
        $this->user_id = $userId;
    }

    /**
     * @return Uuid
     */
    public function getRecipeId()
    {
        return $this->recipe_id;
    }

    /**
     * @param Uuid $recipe_id
     * @return RecipeUser
     */
    public function setRecipeId($recipe_id)
    {
        $this->recipe_id = $recipe_id;
        return $this;
    }

    /**
     * @return Uuid
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param Uuid $user_id
     * @return RecipeUser
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

}
