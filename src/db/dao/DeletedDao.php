<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;

class DeletedDao extends BaseDao
{

    public function getDeletedSinceByTableName($lastUpdate, $tableName)
    {
        $sql = "SELECT BIN_TO_UUID(d.deleted_id, 1) AS id 
                FROM " . Config::TABLE_DELETED . " d 
                INNER JOIN " . Config::TABLE_DELETED_TABLE . " t ON d.table_id = t.id
                WHERE t.name = :tableName AND d.deleted > :lastUpdate";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":lastUpdate", $lastUpdate);
        $stmt->bindValue(":tableName", $tableName);
        if (!$stmt->execute()) {
            return false;
        }
        return $this->getColumn($stmt, "id");
    }

    public function deleteByIdsAndTableName(array $ids, $tableName)
    {
        if (!empty($ids)) {
            $inQuery = implode(',', array_fill(0, count($ids), 'UUID_TO_BIN(?, 1)'));
            $sql = "DELETE FROM $tableName WHERE id in ($inQuery)";
            $stmt = $this->db->prepare($sql);
            foreach ($ids as $k => $id) {
                $stmt->bindValue(($k + 1), $id);
            }
            if (!$stmt->execute()) {
                return false;
            }

            $this->addDeletedRecords($ids, $tableName);
        }

        return true;
    }

    public function addDeletedRecord($id, $tableName)
    {
        $sql = "INSERT INTO " . Config::TABLE_DELETED . " (table_id, deleted_id) 
                SELECT id AS table_id, UUID_TO_BIN(:deleted_id , 1) AS deleted_id
                FROM " . Config::TABLE_DELETED_TABLE . "
                WHERE name = :table_name";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":deleted_id", $id);
        $stmt->bindValue(":table_name", $tableName);
        if (!$stmt->execute()) {
            return false;
        }
        $this->updateLastUpdate();
        return true;
    }

    private function addDeletedRecords(array $ids, $tableName)
    {
        $result = true;
        foreach ($ids as $id) {
            if (!$this->addDeletedRecord($id, $tableName)) {
                $result = false;
            }
        }
        return $result;
    }

    private function updateLastUpdate()
    {
        $this->updateLastUpdateByTableAndColumnName(Config::TABLE_DELETED, "deleted");
    }
}
