<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;

class SyncDao extends BaseDao
{
    public function upsert($lastUpdate)
    {
        $sql = "INSERT INTO " . Config::TABLE_SYNC . " (id, last_update)
            VALUES (1, :lastUpdate)
            ON DUPLICATE KEY UPDATE last_update = :lastUpdate";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":lastUpdate", $lastUpdate);
        return $stmt->execute();
    }

    public function updateLastUpdateIfNewer($lastUpdate)
    {
        $currentLastUpdate = $this->getLastUpdate();
        if (!isset($currentLastUpdate) || $lastUpdate > $currentLastUpdate) {
            return $this->upsert($lastUpdate);
        }
        return false;
    }

    public function getLastUpdate()
    {
        $sql = "SELECT last_update FROM " . Config::TABLE_SYNC . " WHERE id = 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute()) {
            return false;
        }
        return $this->getFirstValue($stmt, "last_update");
    }
}
