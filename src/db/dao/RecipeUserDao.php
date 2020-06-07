<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

namespace Recipes\db\dao;

use Recipes\config\Config;
use Recipes\db\entity\RecipeUser;

class RecipeUserDao extends BaseDao
{
    public function insert(RecipeUser $recipeUser)
    {
        $sql = "INSERT INTO " . Config::TABLE_RECIPE_USER . " 
            (id, recipe_id, user_id) 
            VALUES (
                UUID_TO_BIN(:id, 1), 
                UUID_TO_BIN(:recipe_id, 1), 
                UUID_TO_BIN(:user_id, 1) 
            )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipeUser->getId());
        $stmt->bindValue(":recipe_id", $recipeUser->getRecipeId());
        $stmt->bindValue(":user_id", $recipeUser->getUserId());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }

    public function update(RecipeUser $recipeUser)
    {
        $sql = "UPDATE " . Config::TABLE_RECIPE_USER . " 
            SET
                recipe_id = UUID_TO_BIN(:recipe_id, 1), 
                user_id = UUID_TO_BIN(:user_id, 1) 
            WHERE
                id = UUID_TO_BIN(:id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $recipeUser->getId());
        $stmt->bindValue(":recipe_id", $recipeUser->getRecipeId());
        $stmt->bindValue(":user_id", $recipeUser->getUserId());

        if (!$stmt->execute()) {
            return false;
        }

        // TODO $this->updateLastUpdate();

        return true;
    }
}
