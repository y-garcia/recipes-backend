<?php
/**
 * Author: Yeray García Quintana
 * Date: 30.10.2018
 */

namespace Recipes\model;

class SyncService
{
    private $aisleDao;
    private $unitDao;
    private $tagDao;
    private $ingredientDao;
    private $recipeDao;
    private $recipeIngredientDao;
    private $recipeStepDao;
    private $recipeTagDao;
    private $syncDao;

    private $userId;

    /**
     * SyncService constructor.
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->aisleDao = $recipeRepository->getAisleDao();
        $this->unitDao = $recipeRepository->getUnitDao();
        $this->tagDao = $recipeRepository->getTagDao();
        $this->ingredientDao = $recipeRepository->getIngredientDao();
        $this->recipeDao = $recipeRepository->getRecipeDao();
        $this->recipeIngredientDao = $recipeRepository->getRecipeIngredientDao();
        $this->recipeStepDao = $recipeRepository->getRecipeStepDao();
        $this->recipeTagDao = $recipeRepository->getRecipeTagDao();
        $this->syncDao = $recipeRepository->getSyncDao();
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function syncData(SyncDto $clientSyncData)
    {
        $this->mergeClientDataIntoDb($clientSyncData, $this->userId);
        return $this->getDataToSync($clientSyncData->getLastUpdate(), $this->userId);
    }

    private function mergeClientDataIntoDb(SyncDto $data, $userId)
    {
        $this->recipeTagDao->deleteByIdsAndUser($data->getDeletedRecipeTags(), $userId);
        $this->recipeStepDao->deleteByIdsAndUser($data->getDeletedRecipeSteps(), $userId);
        $this->recipeIngredientDao->deleteByIdsAndUser($data->getDeletedRecipeIngredients(), $userId);
        $this->recipeDao->deleteByIdsAndUser($data->getDeletedRecipes(), $userId);
        $this->ingredientDao->deleteByIds($data->getDeletedIngredients());
        $this->tagDao->deleteByIds($data->getDeletedTags());
        $this->unitDao->deleteByIds($data->getDeletedUnits());
        $this->aisleDao->deleteByIds($data->getDeletedAisles());

        $this->aisleDao->upsertAll($data->getAisles());
        $this->unitDao->upsertAll($data->getUnits());
        $this->tagDao->upsertAll($data->getTags());
        $this->ingredientDao->upsertAll($data->getIngredients());
        $this->recipeDao->upsertAllForUser($data->getRecipes(), $userId);
        $this->recipeIngredientDao->upsertAllForUser($data->getRecipeIngredients(), $userId);
        $this->recipeStepDao->upsertAllForUser($data->getRecipeSteps(), $userId);
        $this->recipeTagDao->upsertAllForUser($data->getRecipeTags(), $userId);
    }

    private function getDataToSync($lastUpdate, $userId)
    {
        $result = new SyncDto();

        $result->setLastUpdate($this->syncDao->getLastUpdate());

        // TODO (1) make sure the user has access to the returned data
        $result->setAisles($this->aisleDao->getChangesSince($lastUpdate));
        $result->setUnits($this->unitDao->getChangesSince($lastUpdate));
        $result->setTags($this->tagDao->getChangesSince($lastUpdate));
        $result->setIngredients($this->ingredientDao->getChangesSince($lastUpdate));
        $result->setRecipes($this->recipeDao->getChangesSince($lastUpdate));
        $result->setRecipeIngredients($this->recipeIngredientDao->getChangesSince($lastUpdate));
        $result->setRecipeSteps($this->recipeStepDao->getChangesSince($lastUpdate));
        $result->setRecipeTags($this->recipeTagDao->getChangesSince($lastUpdate));

        $result->setDeletedAisles($this->aisleDao->getDeletedSince($lastUpdate));
        $result->setDeletedUnits($this->unitDao->getDeletedSince($lastUpdate));
        $result->setDeletedTags($this->tagDao->getDeletedSince($lastUpdate));
        $result->setDeletedIngredients($this->ingredientDao->getDeletedSince($lastUpdate));
        $result->setDeletedRecipes($this->recipeDao->getDeletedSince($lastUpdate));
        $result->setDeletedRecipeIngredients($this->recipeIngredientDao->getDeletedSince($lastUpdate));
        $result->setDeletedRecipeSteps($this->recipeStepDao->getDeletedSince($lastUpdate));
        $result->setDeletedRecipeTags($this->recipeTagDao->getDeletedSince($lastUpdate));

        return $result;
    }
}
