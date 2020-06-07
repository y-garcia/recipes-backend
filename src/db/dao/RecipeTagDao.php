<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;
use Recipes\db\entity\RecipeTag;

class RecipeTagDao extends BaseDao
{
    public function insert(RecipeTag $recipeTag)
    {
        $sql = "INSERT INTO " . Config::TABLE_RECIPE_TAG . " 
            (id, recipe_id, tag_id) 
            VALUES (
                UUID_TO_BIN(:id, 1), 
                UUID_TO_BIN(:recipe_id, 1), 
                UUID_TO_BIN(:tag_id, 1) 
            )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipeTag->getId());
        $stmt->bindValue(":recipe_id", $recipeTag->getRecipeId());
        $stmt->bindValue(":tag_id", $recipeTag->getTagId());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function update(RecipeTag $recipeTag)
    {
        $sql = "UPDATE " . Config::TABLE_RECIPE_TAG . " 
            SET
                recipe_id = UUID_TO_BIN(:recipe_id, 1), 
                tag_id = UUID_TO_BIN(:tag_id, 1) 
            WHERE
                id = UUID_TO_BIN(:id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipeTag->getId());
        $stmt->bindValue(":recipe_id", $recipeTag->getRecipeId());
        $stmt->bindValue(":tag_id", $recipeTag->getTagId());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function upsert(RecipeTag $recipeTag)
    {
        /** @var RecipeTag $entityById */
        $entityById = $this->findById($recipeTag);
        /** @var RecipeTag $entityByRecipeAndTag */
        $entityByRecipeAndTag = $this->findByRecipeAndTag($recipeTag);

        if ($entityById == null && $entityByRecipeAndTag == null) {
            // no record found, insert the new one
            $result = $this->insert($recipeTag);
        } else if ($entityById == null && $entityByRecipeAndTag != null) {
            // no record found with the same id, but with the same recipe/tag combination
            // update timestamp of existing one (to force a client update) and delete the new one (since no longer needed)
            $result = $this->update($entityByRecipeAndTag) && $this->deletedDao->addDeletedRecord($recipeTag->getId(), $recipeTag->getTable());
        } else if ($entityById != null && $entityByRecipeAndTag == null) {
            // no record found with the recipe/tag combination, but with the same id
            // update timestamp of existing id (to force a client update) and add the new one with a new id
            $recipeTag->setId(null);
            $result = $this->update($entityById) && $this->insert($recipeTag);
        } else if ($entityById->getId()->equals($entityByRecipeAndTag->getId())) {
            // if we have found the exact match, update it
            $result = $this->update($recipeTag);
        } else {
            // we have found a record with the same id, and a different record with the recipe/tag combination
            // update both timestamps to force a client update
            $result = $this->update($entityById) && $this->update($entityByRecipeAndTag);
        }

        return $result;
    }

    protected function findByRecipeAndTag(RecipeTag $recipeTag)
    {
        $sql = "SELECT
                BIN_TO_UUID(id, 1) AS id, 
                BIN_TO_UUID(recipe_id, 1) AS recipe_id, 
                BIN_TO_UUID(tag_id, 1) AS tag_id
            FROM " . Config::TABLE_RECIPE_TAG . " 
            WHERE
                recipe_id = UUID_TO_BIN(:recipe_id, 1)
                AND tag_id = UUID_TO_BIN(:tag_id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeTag->getRecipeId());
        $stmt->bindValue(":tag_id", $recipeTag->getTagId());

        if (!$stmt->execute()) {
            return null;
        }

        return $this->getFirstObject($stmt);
    }

    public function upsertAllForUser(array $recipeTags, $userId)
    {
        $result = true;
        /** @var RecipeTag $recipeTag */
        foreach ($recipeTags as $recipeTag) {
            $canEdit = !$this->exists($recipeTag) || $this->hasUserAccessToRecipe($recipeTag->getRecipeId(), $userId);
            if ($canEdit && !$this->upsert($recipeTag)) {
                $result = false;
            }
        }

        // TODO $this->updateLastUpdate();

        return $result;
    }

    public function getChangesSince($lastUpdate)
    {
        return $this->getTableChangesSince($lastUpdate,
            // TODO add created and modified columns
            "SELECT BIN_TO_UUID(id, 1) AS id, BIN_TO_UUID(recipe_id, 1) AS recipe_id
                FROM " . Config::TABLE_RECIPE_TAG . " WHERE :lastUpdate = :lastUpdate");
    }

    public function getDeletedSince($lastUpdate)
    {
        return $this->deletedDao->getDeletedSinceByTableName($lastUpdate, Config::TABLE_RECIPE_TAG);
    }

    public function deleteByIdsAndUser(array $ids, $userId)
    {
        $filteredIds = $this->filterIdsByUserId($ids, $userId);
        return $this->deletedDao->deleteByIdsAndTableName($filteredIds, Config::TABLE_RECIPE_TAG);
    }

    public function filterIdsByUserId(array $ids, $userId)
    {
        if (empty($ids)) {
            return array();
        }

        $inQuery = implode(',', array_fill(0, count($ids), 'UUID_TO_BIN(?, 1)'));
        $sql =
            "SELECT BIN_TO_UUID(rt.id, 1) AS id
            FROM " . Config::TABLE_RECIPE . " r
            INNER JOIN " . Config::TABLE_RECIPE_TAG . " rt ON r.id = rt.recipe_id
            INNER JOIN " . Config::TABLE_RECIPE_USER . " u ON r.id = u.recipe_id
            WHERE u.user_id = UUID_TO_BIN(?, 1) AND rt.id in ($inQuery)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $userId);
        foreach ($ids as $k => $id) {
            $stmt->bindValue(($k + 2), $id);
        }

        if (!$stmt->execute()) {
            return false;
        }

        return $this->getColumn($stmt, "id");
    }
}
