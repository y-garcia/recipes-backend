<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;
use Recipes\db\entity\RecipeStep;

class RecipeStepDao extends BaseDao
{
    public function insert(RecipeStep $recipeStep)
    {
        $sql = "INSERT INTO " . Config::TABLE_RECIPE_STEP . " 
            (id, recipe_id, description, is_section, sort_order) 
            VALUES (
                UUID_TO_BIN(:id, 1), 
                UUID_TO_BIN(:recipe_id, 1), 
                :description,
                :is_section,
                :sort_order
            )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipeStep->getId());
        $stmt->bindValue(":recipe_id", $recipeStep->getRecipeId());
        $stmt->bindValue(":description", $recipeStep->getDescription());
        $stmt->bindValue(":is_section", $recipeStep->getIsSection());
        $stmt->bindValue(":sort_order", $recipeStep->getSortOrder());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function update(RecipeStep $recipeStep)
    {
        $sql = "UPDATE " . Config::TABLE_RECIPE_STEP . " 
            SET
                recipe_id = UUID_TO_BIN(:recipe_id, 1), 
                description = :description,
                is_section = :is_section,
                sort_order :sort_order
            WHERE
                id = UUID_TO_BIN(:id, 1), 
            ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipeStep->getId());
        $stmt->bindValue(":recipe_id", $recipeStep->getRecipeId());
        $stmt->bindValue(":description", $recipeStep->getDescription());
        $stmt->bindValue(":is_section", $recipeStep->getIsSection());
        $stmt->bindValue(":sort_order", $recipeStep->getSortOrder());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function upsert(RecipeStep $recipeStep)
    {
        return $this->exists($recipeStep) ? $this->update($recipeStep) : $this->insert($recipeStep);
    }

    public function upsertAllForUser(array $recipeSteps, $userId)
    {
        $result = true;
        /** @var RecipeStep $recipeStep */
        foreach ($recipeSteps as $recipeStep) {
            $canEdit = !$this->exists($recipeStep) || $this->hasUserAccessToRecipe($recipeStep->getRecipeId(), $userId);
            if ($canEdit && !$this->upsert($recipeStep)) {
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
            "SELECT 
                    BIN_TO_UUID(id, 1) AS id,
                    BIN_TO_UUID(recipe_id, 1) AS recipe_id,
                    description,
                    is_section,
                    sort_order
                FROM " . Config::TABLE_RECIPE_STEP . " WHERE :lastUpdate = :lastUpdate");
    }

    public function getDeletedSince($lastUpdate)
    {
        return $this->deletedDao->getDeletedSinceByTableName($lastUpdate, Config::TABLE_RECIPE_STEP);
    }

    public function deleteByIdsAndUser(array $ids, $userId)
    {
        $filteredIds = $this->filterIdsByUserId($ids, $userId);
        return $this->deletedDao->deleteByIdsAndTableName($filteredIds, Config::TABLE_RECIPE_STEP);
    }

    public function filterIdsByUserId(array $ids, $userId)
    {
        if (empty($ids)) {
            return array();
        }

        $inQuery = implode(',', array_fill(0, count($ids), 'UUID_TO_BIN(?, 1)'));
        $sql =
            "SELECT BIN_TO_UUID(rs.id, 1) AS id
            FROM " . Config::TABLE_RECIPE . " r
            INNER JOIN " . Config::TABLE_RECIPE_STEP . " rs ON r.id = rs.recipe_id
            INNER JOIN " . Config::TABLE_RECIPE_USER . " u ON r.id = u.recipe_id
            WHERE u.user_id = UUID_TO_BIN(?, 1) AND rs.id in ($inQuery)";

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
