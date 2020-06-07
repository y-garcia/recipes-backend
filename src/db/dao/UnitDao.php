<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;
use Recipes\db\entity\Unit;

class UnitDao extends BaseDao
{
    public function insert(Unit $unit)
    {
        $sql = "INSERT INTO " . Config::TABLE_UNIT . " (id, name_singular, name_plural)
            VALUES (UUID_TO_BIN(:id, 1), :name_singular, :name_plural)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $unit->getId());
        $stmt->bindValue(":name_singular", $unit->getNameSingular());
        $stmt->bindValue(":name_plural", $unit->getNamePlural());
        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function update(Unit $unit)
    {
        $sql = "UPDATE " . Config::TABLE_UNIT . "
            SET
                name_singular = :name_singular,
                name_plural = :name_plural
            WHERE
                id = UUID_TO_BIN(:id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $unit->getId());
        $stmt->bindValue(":name_singular", $unit->getNameSingular());
        $stmt->bindValue(":name_plural", $unit->getNamePlural());
        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function upsert(Unit $unit)
    {
        return $this->exists($unit) ? $this->update($unit) : $this->insert($unit);
    }

    /**
     * @param Unit[] $units
     * @param $userId
     * @return bool
     */
    public function upsertAll(array $units)
    {
        $result = true;
        foreach ($units as $unit) {
            if (!$this->upsert($unit)) {
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
            "SELECT BIN_TO_UUID(id, 1) AS id, name_singular, name_plural FROM " . Config::TABLE_UNIT . " WHERE :lastUpdate = :lastUpdate");
    }

    public function getDeletedSince($lastUpdate)
    {
        return $this->deletedDao->getDeletedSinceByTableName($lastUpdate, Config::TABLE_UNIT);
    }

    public function deleteByIds($ids)
    {
        return $this->deletedDao->deleteByIdsAndTableName($ids, Config::TABLE_UNIT);
    }
}
