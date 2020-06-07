<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;
use Recipes\db\entity\Recipe;

class RecipeDao extends BaseDao
{
    public function insert(Recipe $recipe)
    {
        $sql = "INSERT INTO " . Config::TABLE_RECIPE . " (id, name, portions, duration, url) 
            VALUES (UUID_TO_BIN(:id, 1), :name, :portions, :duration, :url)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipe->getId());
        $stmt->bindValue(":name", $recipe->getName());
        $stmt->bindValue(":portions", $recipe->getPortions());
        $stmt->bindValue(":duration", $recipe->getDuration());
        $stmt->bindValue(":url", $recipe->getUrl());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function update(Recipe $recipe)
    {
        $sql = "UPDATE " . Config::TABLE_RECIPE . "
            SET name = :name, portions = :portions, duration = :duration, url = :url)
            WHERE id = UUID_TO_BIN(:id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipe->getId());
        $stmt->bindValue(":name", $recipe->getName());
        $stmt->bindValue(":portions", $recipe->getPortions());
        $stmt->bindValue(":duration", $recipe->getDuration());
        $stmt->bindValue(":url", $recipe->getUrl());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function upsert(Recipe $recipe)
    {
        return $this->exists($recipe) ? $this->update($recipe) : $this->insert($recipe);
    }

    public function upsertAllForUser(array $recipes, $userId)
    {
        $result = true;
        foreach ($recipes as $recipe) {
            $canEdit = !$this->exists($recipe) || $this->hasUserAccessToRecipe($recipe->getId(), $userId);
            if ($canEdit && !$this->upsert($recipe)) {
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
            "SELECT BIN_TO_UUID(id, 1) AS id, name, portions, duration, url FROM " . Config::TABLE_RECIPE . " WHERE :lastUpdate = :lastUpdate");
    }

    public function getDeletedSince($lastUpdate)
    {
        return $this->deletedDao->getDeletedSinceByTableName($lastUpdate, Config::TABLE_RECIPE);
    }

    public function deleteByIdsAndUser(array $ids, $userId)
    {
        $filteredIds = $this->filterIdsByUserId($ids, $userId);
        return $this->deletedDao->deleteByIdsAndTableName($filteredIds, Config::TABLE_RECIPE);
    }

    public function filterIdsByUserId(array $ids, $userId)
    {
        if (empty($ids)) {
            return array();
        }

        $inQuery = implode(',', array_fill(0, count($ids), 'UUID_TO_BIN(?, 1)'));
        $sql =
            "SELECT BIN_TO_UUID(r.id, 1) AS id
            FROM " . Config::TABLE_RECIPE . " r
            INNER JOIN " . Config::TABLE_RECIPE_USER . " u ON r.id = u.recipe_id
            WHERE u.user_id = UUID_TO_BIN(?, 1) AND r.id in ($inQuery)";

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
