<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;
use Recipes\db\entity\Aisle;

class AisleDao extends BaseDao
{
    public function insert(Aisle $aisle)
    {
        $sql = "INSERT INTO " . Config::TABLE_AISLE . " (id, name)
            VALUES (UUID_TO_BIN(:id, 1), :name)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $aisle->getId());
        $stmt->bindValue(":name", $aisle->getName());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function update(Aisle $aisle)
    {
        $sql = "UPDATE " . Config::TABLE_AISLE . "
            SET
                name = :name
            WHERE
                id = UUID_TO_BIN(:id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $aisle->getId());
        $stmt->bindValue(":name", $aisle->getName());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function upsert(Aisle $tag)
    {
        return $this->exists($tag) ? $this->update($tag) : $this->insert($tag);
    }

    /**
     * @param Aisle[] $aisles
     * @return bool
     */
    public function upsertAll(array $aisles)
    {
        $result = true;
        foreach ($aisles as $aisle) {
            if (!$this->upsert($aisle)) {
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
            "SELECT BIN_TO_UUID(id, 1) AS id, name FROM " . Config::TABLE_AISLE . " WHERE :lastUpdate = :lastUpdate");
    }

    public function getDeletedSince($lastUpdate)
    {
        return $this->deletedDao->getDeletedSinceByTableName($lastUpdate, Config::TABLE_AISLE);
    }

    public function deleteByIds($ids)
    {
        return $this->deletedDao->deleteByIdsAndTableName($ids, Config::TABLE_AISLE);
    }
}
