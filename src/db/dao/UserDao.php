<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;
use Recipes\db\entity\User;

class UserDao extends BaseDao
{
    public function insert(User $user)
    {
        $sql = "INSERT INTO " . Config::TABLE_USER . " (id, username, password_hash, given_name, family_name) 
        VALUES (UUID_TO_BIN(:id, 1), :username, :password_hash, :given_name, :family_name)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $user->getId());
        $stmt->bindValue(":username", $user->getUsername());
        $stmt->bindValue(":password_hash", $user->getPasswordHash());
        $stmt->bindValue(":given_name", $user->getGivenName());
        $stmt->bindValue(":family_name", $user->getFamilyName());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function update(User $user)
    {
        $sql = "UPDATE " . Config::TABLE_USER . " 
        SET
            username = :username, 
            password_hash = :password_hash, 
            given_name = :given_name, 
            family_name = :family_name
        WHERE
            id = (UUID_TO_BIN(:id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $user->getId());
        $stmt->bindValue(":username", $user->getUsername());
        $stmt->bindValue(":password_hash", $user->getPasswordHash());
        $stmt->bindValue(":given_name", $user->getGivenName());
        $stmt->bindValue(":family_name", $user->getFamilyName());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function upsert(User $user)
    {
        return $this->exists($user) ? $this->update($user) : $this->insert($user);
    }

    /**
     * @param User[] $users
     * @return bool
     */
    public function upsertAll(array $users)
    {
        $result = true;
        foreach ($users as $user) {
            if (!$this->update($user)) {
                $result = false;
            }
        }
        return $result;
    }

    public function getChangesSince($lastUpdate)
    {
        return $this->getTableChangesSince($lastUpdate,
            // TODO add created and modified columns
            "SELECT BIN_TO_UUID(id, 1) AS id, username, password_hash, given_name, family_name FROM " . Config::TABLE_USER . " WHERE :lastUpdate = :lastUpdate");
    }

    public function getDeletedSince($lastUpdate)
    {
        return $this->deletedDao->getDeletedSinceByTableName($lastUpdate, Config::TABLE_USER);
    }

    public function deleteByIds(array $ids)
    {
        return $this->deletedDao->deleteByIdsAndTableName($ids, Config::TABLE_USER);
    }
}
