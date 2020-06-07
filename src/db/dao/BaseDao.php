<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use PDO;
use PDOStatement;
use Recipes\config\Config;
use Recipes\db\entity\Entity;

class BaseDao
{
    protected $db;
    protected $syncDao;
    protected $deletedDao;

    private $entityType = null;

    /**
     * AisleDao constructor.
     * @param $entityType
     * @param PDO $db
     * @param SyncDao $syncDao
     * @param DeletedDao $deletedDao
     */
    public function __construct($entityType, PDO $db, SyncDao $syncDao = null, DeletedDao $deletedDao = null)
    {
        $this->entityType = Config::ENTITY_PATH . $entityType;
        $this->db = $db;
        $this->syncDao = $syncDao;
        $this->deletedDao = $deletedDao;
    }

    /**
     * @return Entity[]|bool
     */
    public function findAll()
    {
        /** @var Entity $entity */
        $entity = new $this->entityType();
        $sql = "SELECT * FROM " . $entity->getTable();
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute()) {
            return false;
        }
        return $this->getObjects($stmt);
    }

    public function delete(Entity $entity)
    {
        $sql = "DELETE FROM " . $entity->getTable() . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $entity->getId());

        if (!$stmt->execute()) {
            return false;
        }

        $this->deletedDao->addDeletedRecord($entity->getId(), $entity->getTable());

        return true;
    }

    protected function exists(Entity $entity)
    {
        $sql = "SELECT 1 AS entity_exists FROM " . $entity->getTable() . " WHERE id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $entity->getId());
        if (!$stmt->execute()) {
            return false;
        }
        return $this->getFirstValue($stmt, "entity_exists") == 1;
    }

    protected function findById(Entity $entity)
    {
        $sql = "SELECT * FROM " . $entity->getTable() . " WHERE id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $entity->getId());
        if (!$stmt->execute()) {
            return false;
        }
        return $this->getFirstObject($stmt);
    }

    protected function updateLastUpdateByTableAndColumnName($tableName, $columnName = "modified")
    {
        $lastUpdate = $this->getMaxTimestampByTableAndColumnName($tableName, $columnName);
        return $this->syncDao->updateLastUpdateIfNewer($lastUpdate);
    }

    protected function getTableChangesSince($lastUpdate, $sql)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":lastUpdate", $lastUpdate);
        if (!$stmt->execute()) {
            return false;
        }
        return $this->entityType ? $this->getObjects($stmt) : $this->getArray($stmt);
    }

    protected function getFirstValue(PDOStatement $statement, $columnName)
    {
        if ($row = $statement->fetch()) {
            return $row[$columnName];
        }
        return null;
    }

    protected function getColumn(PDOStatement $statement, $columnName)
    {
        $column = array();
        while ($row = $statement->fetch()) {
            $column[] = $row[$columnName];
        }
        return $column;
    }

    protected function hasUserAccessToRecipe($recipeId, $userId)
    {
        if ($userId == null || $recipeId == null) {
            return false;
        }

        $sql = "SELECT 1 AS allowed FROM " . Config::TABLE_RECIPE_USER . " WHERE recipe_id = UUID_TO_BIN(:recipe_id, 1) AND user_id = UUID_TO_BIN(:user_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);
        $stmt->bindValue(":user_id", $userId);

        return $stmt->execute() && $this->getFirstValue($stmt, "allowed") == 1;
    }

    private function getObjects(PDOStatement $statement)
    {
        $data = array();
        while ($row = $statement->fetch()) {
            /** @var Entity $entity */
            $entity = new $this->entityType();
            $data[] = $entity->fromArray($row);
        }
        return $data;
    }

    protected function getFirstObject(PDOStatement $statement)
    {
        if ($row = $statement->fetch()) {
            /** @var Entity $entity */
            $entity = new $this->entityType();
            return $entity->fromArray($row);
        }
        return null;
    }

    private function getArray(PDOStatement $statement)
    {
        $data = array();
        while ($row = $statement->fetch()) {
            $data[] = $row;
        }
        return $data;
    }

    private function getMaxTimestampByTableAndColumnName($tableName, $columnName)
    {
        $sql = "SELECT MAX(" . $columnName . ") as modified FROM " . $tableName;
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute()) {
            return false;
        }
        return $this->getFirstValue($stmt, "modified");
    }
}
