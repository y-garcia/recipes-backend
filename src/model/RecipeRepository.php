<?php
/**
 * Author: Yeray García Quintana
 * Date: 30.10.2018
 */

namespace Recipes\model;

use PDO;
use Recipes\db\dao\AisleDao;
use Recipes\db\dao\DeletedDao;
use Recipes\db\dao\IngredientDao;
use Recipes\db\dao\RecipeDao;
use Recipes\db\dao\RecipeIngredientDao;
use Recipes\db\dao\RecipeStepDao;
use Recipes\db\dao\RecipeTagDao;
use Recipes\db\dao\RecipeUserDao;
use Recipes\db\dao\SyncDao;
use Recipes\db\dao\TagDao;
use Recipes\db\dao\UnitDao;
use Recipes\db\dao\UserDao;

class RecipeRepository
{
    private $db;
    private $syncDao;
    private $deletedDao;
    private $aisleDao;
    private $userDao;
    private $unitDao;
    private $tagDao;
    private $ingredientDao;
    private $recipeDao;
    private $recipeIngredientDao;
    private $recipeStepDao;
    private $recipeTagDao;
    private $recipeUserDao;

    /**
     * RecipeRepository constructor.
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->syncDao = new SyncDao("Sync", $this->db);
        $this->deletedDao = new DeletedDao("Deleted", $this->db, $this->syncDao);
        $this->userDao = new UserDao("User", $this->db, $this->syncDao, $this->deletedDao);
        $this->aisleDao = new AisleDao("Aisle", $this->db, $this->syncDao, $this->deletedDao);
        $this->unitDao = new UnitDao("Unit", $this->db, $this->syncDao, $this->deletedDao);
        $this->tagDao = new TagDao("Tag", $this->db, $this->syncDao, $this->deletedDao);
        $this->ingredientDao = new IngredientDao("Ingredient", $this->db, $this->syncDao, $this->deletedDao);
        $this->recipeDao = new RecipeDao("Recipe", $this->db, $this->syncDao, $this->deletedDao);
        $this->recipeIngredientDao = new RecipeIngredientDao("RecipeIngredient", $this->db, $this->syncDao, $this->deletedDao);
        $this->recipeStepDao = new RecipeStepDao("RecipeStep", $this->db, $this->syncDao, $this->deletedDao);
        $this->recipeTagDao = new RecipeTagDao("RecipeTag", $this->db, $this->syncDao, $this->deletedDao);
        $this->recipeUserDao = new RecipeUserDao("RecipeUser", $this->db, $this->syncDao, $this->deletedDao);
    }

    /**
     * @return SyncDao
     */
    public function getSyncDao()
    {
        return $this->syncDao;
    }

    /**
     * @return AisleDao
     */
    public function getAisleDao()
    {
        return $this->aisleDao;
    }

    /**
     * @return UnitDao
     */
    public function getUnitDao()
    {
        return $this->unitDao;
    }

    /**
     * @return TagDao
     */
    public function getTagDao()
    {
        return $this->tagDao;
    }

    /**
     * @return IngredientDao
     */
    public function getIngredientDao()
    {
        return $this->ingredientDao;
    }

    /**
     * @return RecipeDao
     */
    public function getRecipeDao()
    {
        return $this->recipeDao;
    }

    /**
     * @return RecipeIngredientDao
     */
    public function getRecipeIngredientDao()
    {
        return $this->recipeIngredientDao;
    }

    /**
     * @return RecipeStepDao
     */
    public function getRecipeStepDao()
    {
        return $this->recipeStepDao;
    }

    /**
     * @return RecipeTagDao
     */
    public function getRecipeTagDao()
    {
        return $this->recipeTagDao;
    }

    /**
     * @return RecipeUserDao
     */
    public function getRecipeUserDao()
    {
        return $this->recipeUserDao;
    }

    /**
     * @return UserDao
     */
    public function getUserDao()
    {
        return $this->userDao;
    }

    /**
     * @return DeletedDao
     */
    public function getDeletedDao()
    {
        return $this->deletedDao;
    }

}
