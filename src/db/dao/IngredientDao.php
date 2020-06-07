<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;
use Recipes\db\entity\Ingredient;

class IngredientDao extends BaseDao
{
    public function insert(Ingredient $ingredient, $updateLastUpdate = true)
    {
        $sql = "INSERT INTO " . Config::TABLE_INGREDIENT . " (id, name, aisle_id)
            VALUES (UUID_TO_BIN(:id, 1), :name, UUID_TO_BIN(:aisle_id, 1))";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $ingredient->getId());
        $stmt->bindValue(":name", $ingredient->getName());
        $stmt->bindValue(":aisle_id", $ingredient->getAisleId());

        if (!$stmt->execute()) {
            return false;
        }

        if($updateLastUpdate){
            $this->updateLastUpdate();
        }

        return true;
    }

    public function update(Ingredient $ingredient, $updateLastUpdate = true)
    {
        $sql = "UPDATE " . Config::TABLE_INGREDIENT . "
            SET
                name = :name,
                aisle_id = UUID_TO_BIN(:aisle_id, 1)
            WHERE
                id = UUID_TO_BIN(:id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $ingredient->getId());
        $stmt->bindValue(":name", $ingredient->getName());
        $stmt->bindValue(":aisle_id", $ingredient->getAisleId());

        if (!$stmt->execute()) {
            return false;
        }

        if($updateLastUpdate){
            $this->updateLastUpdate();
        }

        return true;
    }

    public function upsert(Ingredient $ingredient, $updateLastUpdate = true)
    {
        return $this->exists($ingredient) ? $this->update($ingredient, $updateLastUpdate) : $this->insert($ingredient, $updateLastUpdate);
    }

    /**
     * @param Ingredient[] $ingredients
     * @return bool
     */
    public function upsertAll(array $ingredients)
    {
        $result = true;
        foreach ($ingredients as $ingredient) {
            if (!$this->upsert($ingredient, false)) {
                $result = false;
            }
        }

        $this->updateLastUpdate();

        return $result;
    }

    public function getChangesSince($lastUpdate)
    {
        return $this->getTableChangesSince($lastUpdate,
            "SELECT BIN_TO_UUID(id, 1) AS id, name, BIN_TO_UUID(aisle_id, 1) AS aisle_id, created, modified 
                  FROM " . Config::TABLE_INGREDIENT . " WHERE modified > :lastUpdate");
    }

    public function getDeletedSince($lastUpdate)
    {
        return $this->deletedDao->getDeletedSinceByTableName($lastUpdate, Config::TABLE_INGREDIENT);
    }

    private function updateLastUpdate()
    {
        $this->updateLastUpdateByTableAndColumnName(Config::TABLE_INGREDIENT);
    }

    public function deleteByIds(array $ids)
    {
        return $this->deletedDao->deleteByIdsAndTableName($ids, Config::TABLE_INGREDIENT);
    }
}
