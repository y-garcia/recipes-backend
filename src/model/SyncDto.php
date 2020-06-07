<?php
/**
 * User;
 * Date;
 * Time;
 */

namespace Recipes\model;


use JsonSerializable;
use Recipes\config\Config;
use Recipes\db\entity\Aisle;
use Recipes\db\entity\Entity;
use Recipes\db\entity\Ingredient;
use Recipes\db\entity\RecipeTag;
use Recipes\db\entity\Tag;
use Recipes\db\entity\Unit;

class SyncDto implements JsonSerializable
{
    /** @var string */
    private $lastUpdate;

    /** @var Aisle[] */
    private $aisles;
    /** @var Unit[] */
    private $units;
    /** @var Tag[] */
    private $tags;
    /** @var Ingredient[] */
    private $ingredients;
    /** @var \Recipes\db\entity\Recipe[] */
    private $recipes;
    /** @var \Recipes\db\entity\RecipeIngredient[] */
    private $recipeIngredients;
    /** @var \Recipes\db\entity\RecipeStep[] */
    private $recipeSteps;
    /** @var RecipeTag[] */
    private $recipeTags;

    /** @var string[] */
    private $deletedAisles;
    /** @var string[] */
    private $deletedUnits;
    /** @var string[] */
    private $deletedTags;
    /** @var string[] */
    private $deletedIngredients;
    /** @var string[] */
    private $deletedRecipes;
    /** @var string[] */
    private $deletedRecipeIngredients;
    /** @var string[] */
    private $deletedRecipeSteps;
    /** @var string[] */
    private $deletedRecipeTags;

    /**
     * SyncData constructor.
     * @param null $jsonArray
     */
    public function __construct($jsonArray = null)
    {
        $this->init();

        if ($jsonArray != null) {
            $entities = array("aisles", "units", "tags", "ingredients", "recipes", "recipeIngredients", "recipeSteps", "recipeTags");
            foreach ($jsonArray as $propName => $propValue) {
                if (in_array($propName, $entities)) {
                    $entityType = Config::ENTITY_PATH . substr(ucfirst($propName), 0, -1);
                    $propValue = $this->getObjects($propValue, $entityType);
                }
                $this->$propName = $propValue;
            }
        }
    }

    private function getObjects(array $array, $entityType)
    {
        $data = array();
        foreach ($array as $item) {
            /** @var Entity $entity */
            $entity = new $entityType();
            $data[] = $entity->fromArray($item);
        }
        return $data;
    }

    private function init()
    {
        $this->lastUpdate = '1900-01-01 00:00:00';

        $this->aisles = array();
        $this->units = array();
        $this->tags = array();
        $this->ingredients = array();
        $this->recipes = array();
        $this->recipeIngredients = array();
        $this->recipeSteps = array();
        $this->recipeTags = array();

        $this->deletedAisles = array();
        $this->deletedUnits = array();
        $this->deletedTags = array();
        $this->deletedIngredients = array();
        $this->deletedRecipes = array();
        $this->deletedRecipeIngredients = array();
        $this->deletedRecipeSteps = array();
        $this->deletedRecipeTags = array();
    }

    /**
     * @return string
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * @param string $lastUpdate
     * @return SyncDto
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }

    /**
     * @return Aisle[]
     */
    public function getAisles()
    {
        return $this->aisles;
    }

    /**
     * @param Aisle[] $aisles
     * @return SyncDto
     */
    public function setAisles($aisles)
    {
        $this->aisles = $aisles;
        return $this;
    }

    /**
     * @return Unit[]
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * @param Unit[] $units
     * @return SyncDto
     */
    public function setUnits($units)
    {
        $this->units = $units;
        return $this;
    }

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag[] $tags
     * @return SyncDto
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return Ingredient[]
     */
    public function getIngredients()
    {
        return $this->ingredients;
    }

    /**
     * @param Ingredient[] $ingredients
     * @return SyncDto
     */
    public function setIngredients($ingredients)
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    /**
     * @return \Recipes\db\entity\Recipe[]
     */
    public function getRecipes()
    {
        return $this->recipes;
    }

    /**
     * @param \Recipes\db\entity\Recipe[] $recipes
     * @return SyncDto
     */
    public function setRecipes($recipes)
    {
        $this->recipes = $recipes;
        return $this;
    }

    /**
     * @return \Recipes\db\entity\RecipeIngredient[]
     */
    public function getRecipeIngredients()
    {
        return $this->recipeIngredients;
    }

    /**
     * @param \Recipes\db\entity\RecipeIngredient[] $recipeIngredients
     * @return SyncDto
     */
    public function setRecipeIngredients($recipeIngredients)
    {
        $this->recipeIngredients = $recipeIngredients;
        return $this;
    }

    /**
     * @return \Recipes\db\entity\RecipeStep[]
     */
    public function getRecipeSteps()
    {
        return $this->recipeSteps;
    }

    /**
     * @param \Recipes\db\entity\RecipeStep[] $recipeSteps
     * @return SyncDto
     */
    public function setRecipeSteps($recipeSteps)
    {
        $this->recipeSteps = $recipeSteps;
        return $this;
    }

    /**
     * @return RecipeTag[]
     */
    public function getRecipeTags()
    {
        return $this->recipeTags;
    }

    /**
     * @param RecipeTag[] $recipeTags
     * @return SyncDto
     */
    public function setRecipeTags($recipeTags)
    {
        $this->recipeTags = $recipeTags;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDeletedAisles()
    {
        return $this->deletedAisles;
    }

    /**
     * @param string[] $deletedAisles
     * @return SyncDto
     */
    public function setDeletedAisles($deletedAisles)
    {
        $this->deletedAisles = $deletedAisles;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDeletedUnits()
    {
        return $this->deletedUnits;
    }

    /**
     * @param string[] $deletedUnits
     * @return SyncDto
     */
    public function setDeletedUnits($deletedUnits)
    {
        $this->deletedUnits = $deletedUnits;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDeletedTags()
    {
        return $this->deletedTags;
    }

    /**
     * @param string[] $deletedTags
     * @return SyncDto
     */
    public function setDeletedTags($deletedTags)
    {
        $this->deletedTags = $deletedTags;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDeletedIngredients()
    {
        return $this->deletedIngredients;
    }

    /**
     * @param string[] $deletedIngredients
     * @return SyncDto
     */
    public function setDeletedIngredients($deletedIngredients)
    {
        $this->deletedIngredients = $deletedIngredients;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDeletedRecipes()
    {
        return $this->deletedRecipes;
    }

    /**
     * @param string[] $deletedRecipes
     * @return SyncDto
     */
    public function setDeletedRecipes($deletedRecipes)
    {
        $this->deletedRecipes = $deletedRecipes;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDeletedRecipeIngredients()
    {
        return $this->deletedRecipeIngredients;
    }

    /**
     * @param string[] $deletedRecipeIngredients
     * @return SyncDto
     */
    public function setDeletedRecipeIngredients($deletedRecipeIngredients)
    {
        $this->deletedRecipeIngredients = $deletedRecipeIngredients;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDeletedRecipeSteps()
    {
        return $this->deletedRecipeSteps;
    }

    /**
     * @param string[] $deletedRecipeSteps
     * @return SyncDto
     */
    public function setDeletedRecipeSteps($deletedRecipeSteps)
    {
        $this->deletedRecipeSteps = $deletedRecipeSteps;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDeletedRecipeTags()
    {
        return $this->deletedRecipeTags;
    }

    /**
     * @param string[] $deletedRecipeTags
     * @return SyncDto
     */
    public function setDeletedRecipeTags($deletedRecipeTags)
    {
        $this->deletedRecipeTags = $deletedRecipeTags;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
