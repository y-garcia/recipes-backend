<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;
use Recipes\db\entity\RecipeIngredient;

class RecipeIngredientDao extends BaseDao
{
    public function insert(RecipeIngredient $recipeIngredient, $updateLastUpdate = true)
    {
        $sql = "INSERT INTO " . Config::TABLE_RECIPE_INGREDIENT . " 
            (id, recipe_id, ingredient_id, quantity, unit_id, sort_order) 
            VALUES (
                UUID_TO_BIN(:id, 1), 
                UUID_TO_BIN(:recipe_id, 1), 
                UUID_TO_BIN(:ingredient_id, 1), 
                :quantity, 
                UUID_TO_BIN(:unit_id, 1), 
                :sort_order
            )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipeIngredient->getId());
        $stmt->bindValue(":recipe_id", $recipeIngredient->getRecipeId());
        $stmt->bindValue(":ingredient_id", $recipeIngredient->getIngredientId());
        $stmt->bindValue(":quantity", $recipeIngredient->getQuantity());
        $stmt->bindValue(":unit_id", $recipeIngredient->getUnitId());
        $stmt->bindValue(":sort_order", $recipeIngredient->getSortOrder());

        if (!$stmt->execute()) {
            return false;
        }

        if ($updateLastUpdate) {
            $this->updateLastUpdate();
        }

        return true;
    }

    public function update(RecipeIngredient $recipeIngredient, $updateLastUpdate = true)
    {
        $sql = "UPDATE " . Config::TABLE_RECIPE_INGREDIENT . " 
            SET
                recipe_id = UUID_TO_BIN(:recipe_id, 1), 
                ingredient_id = UUID_TO_BIN(:ingredient_id, 1), 
                quantity = :quantity, 
                unit_id = UUID_TO_BIN(:unit_id, 1), 
                sort_order = :sort_order
            WHERE
                id = UUID_TO_BIN(:id, 1)
            ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipeIngredient->getId());
        $stmt->bindValue(":recipe_id", $recipeIngredient->getRecipeId());
        $stmt->bindValue(":ingredient_id", $recipeIngredient->getIngredientId());
        $stmt->bindValue(":quantity", $recipeIngredient->getQuantity());
        $stmt->bindValue(":unit_id", $recipeIngredient->getUnitId());
        $stmt->bindValue(":sort_order", $recipeIngredient->getSortOrder());

        if (!$stmt->execute()) {
            return false;
        }

        if ($updateLastUpdate) {
            $this->updateLastUpdate();
        }

        return true;
    }

    public function upsert(RecipeIngredient $recipeIngredient, $updateLastUpdate = true)
    {
        return $this->exists($recipeIngredient) ?
            $this->update($recipeIngredient, $updateLastUpdate) :
            $this->insert($recipeIngredient, $updateLastUpdate);
    }

    public function upsertAllForUser(array $recipeIngredients, $userId)
    {
        $result = true;
        /** @var RecipeIngredient $recipeIngredient */
        foreach ($recipeIngredients as $recipeIngredient) {
            $canEdit = !$this->exists($recipeIngredient) || $this->hasUserAccessToRecipe($recipeIngredient->getRecipeId(), $userId);
            if ($canEdit && !$this->upsert($recipeIngredient, false)) {
                $result = false;
            }
        }

        $this->updateLastUpdate();

        return $result;
    }

    public function getChangesSince($lastUpdate)
    {
        return $this->getTableChangesSince($lastUpdate,
            "SELECT 
                  BIN_TO_UUID(id, 1) AS id, 
                  BIN_TO_UUID(recipe_id, 1) AS recipe_id, 
                  BIN_TO_UUID(ingredient_id, 1) AS ingredient_id, 
                  quantity, 
                  BIN_TO_UUID(unit_id, 1) AS unit_id, 
                  sort_order, 
                  created, 
                  modified
                FROM " . Config::TABLE_RECIPE_INGREDIENT . " WHERE modified > :lastUpdate");
    }

    public function getDeletedSince($lastUpdate)
    {
        return $this->deletedDao->getDeletedSinceByTableName($lastUpdate, Config::TABLE_RECIPE_INGREDIENT);
    }

    private function updateLastUpdate()
    {
        $this->updateLastUpdateByTableAndColumnName(Config::TABLE_RECIPE_INGREDIENT);
    }

    public function deleteByIdsAndUser(array $ids, $userId)
    {
        $filteredIds = $this->filterIdsByUserId($ids, $userId);
        return $this->deletedDao->deleteByIdsAndTableName($filteredIds, Config::TABLE_RECIPE_INGREDIENT);
    }

    public function filterIdsByUserId(array $ids, $userId)
    {
        if (empty($ids)) {
            return array();
        }

        $inQuery = implode(',', array_fill(0, count($ids), 'UUID_TO_BIN(?, 1)'));
        $sql =
            "SELECT BIN_TO_UUID(ri.id, 1) AS id
            FROM " . Config::TABLE_RECIPE . " r
            INNER JOIN " . Config::TABLE_RECIPE_INGREDIENT . " ri ON r.id = ri.recipe_id
            INNER JOIN " . Config::TABLE_RECIPE_USER . " u ON r.id = u.recipe_id
            WHERE u.user_id = UUID_TO_BIN(?, 1) AND ri.id in ($inQuery)";

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
