<?php
/**
 * Author: Yeray García Quintana
 * Date: 30.06.2018
 */

namespace Recipes\model;

use PDO;
use PDOStatement;
use Ramsey\Uuid\Uuid;

class RecipeService
{
    private $error = array();
    private $db;
    private $userId;

    public function __construct(PDO $db, User $userId = null)
    {
        $this->db = $db;
        $this->userId = $userId;
    }

    /**
     * @return User
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param User $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getUsers()
    {
        $sql = "SELECT BIN_TO_UUID(id, 1) AS id, given_name FROM ab_user";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $this->getArray($stmt);
    }

    public function getTags()
    {
        $sql = "SELECT BIN_TO_UUID(id, 1) AS id, name FROM ab_tag";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $this->getArray($stmt);
    }

    public function getDatabase()
    {
        return $this->db;
    }

    private function getArray(PDOStatement $statement)
    {
        $data = array();
        while ($row = $statement->fetch()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getAllForUser($timestamp = '1900-01-01 00:00:00')
    {
        $data = array();

        if ($this->isInvalidId($this->userId)) {
            return $data;
        }

        $data["aisles"] = $this->getArray($this->db->query("SELECT BIN_TO_UUID(id, 1) AS id, name FROM ab_aisle"));
        $data["units"] = $this->getArray($this->db->query("SELECT BIN_TO_UUID(id, 1) AS id, name_singular, name_plural FROM ab_unit"));
        $data["tags"] = $this->getArray($this->db->query("SELECT BIN_TO_UUID(id, 1) AS id, name, usage_count, UNIX_TIMESTAMP(last_used) AS last_used FROM ab_tag"));
        $data["ingredients"] = $this->getIngredients($timestamp);
        $data["recipes"] = $this->getRecipesByUserId($this->userId);
        $data["recipeIngredients"] = $this->getRecipeIngredientByUserId($this->userId, $timestamp);
        $data["recipeSteps"] = $this->getRecipeStepsByUserId($this->userId);
        $data["recipeTags"] = $this->getRecipeTagsByUserId($this->userId);

        return $data;
    }

    private function getIngredients($timestamp)
    {
        $sql =
            "SELECT BIN_TO_UUID(id, 1) AS id, name, BIN_TO_UUID(aisle_id, 1) AS aisle_id
            FROM ab_ingredient 
            WHERE modified > :timestamp";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":timestamp", $timestamp);
        $stmt->execute();
        return $this->getArray($stmt);
    }

    public function getRecipesByUserId($userId)
    {
        $sql =
            "SELECT BIN_TO_UUID(r.id, 1) AS id, r.name, r.portions, r.duration, r.url
            FROM ab_recipe r
            INNER JOIN ab_recipe_user u ON r.id = u.recipe_id
            WHERE u.user_id = UUID_TO_BIN(:user_id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $this->getArray($stmt);
    }

    public function getRecipeById($id)
    {
        $sql = "SELECT BIN_TO_UUID(id, 1) AS id, name, portions, duration, url FROM ab_recipe WHERE id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id);

        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetch();
    }

    public function getRecipeIngredientsByRecipeId($recipeId)
    {
        $sql = "SELECT
                concat(IFNULL(concat(ri.quantity * r.portions, ' '), ''), IFNULL(concat(u.name_plural, ' '), ''), i.name) AS label
            FROM
              ab_recipe_ingredient ri
              INNER JOIN ab_ingredient i ON ri.ingredient_id = i.id
              LEFT OUTER JOIN ab_unit u ON ri.unit_id = u.id
              INNER JOIN ab_recipe r ON ri.recipe_id = r.id
            WHERE recipe_id = UUID_TO_BIN(:recipe_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);

        if (!$stmt->execute()) {
            return false;
        }

        $data = array();
        while ($row = $stmt->fetch()) {
            $data[] = $row['label'];
        }
        return $data;
    }

    private function getRecipeIngredientByUserId($userId, $timestamp = '1970-01-01 00:00:00')
    {
        $sql =
            "SELECT BIN_TO_UUID(ri.id, 1) AS id, BIN_TO_UUID(ri.recipe_id, 1) AS recipe_id, BIN_TO_UUID(ri.ingredient_id, 1) AS ingredient_id, BIN_TO_UUID(ri.unit_id, 1) AS unit_id, ri.quantity, ri.sort_order
            FROM ab_recipe r
            INNER JOIN ab_recipe_ingredient ri ON r.id = ri.recipe_id
            INNER JOIN ab_recipe_user u ON r.id = u.recipe_id
            WHERE u.user_id = UUID_TO_BIN(:user_id, 1) AND ri.modified > :timestamp";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":timestamp", $timestamp);
        $stmt->execute();
        return $this->getArray($stmt);
    }

    private function getRecipeStepsByUserId($userId)
    {
        $sql =
            "SELECT BIN_TO_UUID(rs.id, 1) AS id, BIN_TO_UUID(rs.recipe_id, 1) AS recipe_id, rs.description, rs.is_section, rs.sort_order
            FROM ab_recipe r
            INNER JOIN ab_recipe_step rs ON r.id = rs.recipe_id
            INNER JOIN ab_recipe_user u ON r.id = u.recipe_id
            WHERE u.user_id = UUID_TO_BIN(:user_id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $this->getArray($stmt);
    }

    public function getRecipeStepsByRecipeId($recipeId)
    {
        $sql = "SELECT description AS label FROM ab_recipe_step WHERE recipe_id = UUID_TO_BIN(:recipe_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);

        if (!$stmt->execute()) {
            return false;
        }

        $data = array();
        while ($row = $stmt->fetch()) {
            $data[] = $row['label'];
        }
        return $data;
    }

    public function getRecipeUsersByRecipeId($recipeId)
    {
        $sql = "SELECT BIN_TO_UUID(u.id, 1) AS id, u.given_name FROM ab_recipe_user ru INNER JOIN ab_user u ON ru.user_id = u.id WHERE ru.recipe_id = UUID_TO_BIN(:recipe_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);

        if (!$stmt->execute()) {
            return false;
        }

        return $this->getArray($stmt);
    }

    public function getRecipeTagsByRecipeId($recipeId)
    {
        $sql = "SELECT BIN_TO_UUID(t.id, 1) AS id, t.name FROM ab_recipe_tag rt INNER JOIN ab_tag t ON rt.tag_id = t.id WHERE rt.recipe_id = UUID_TO_BIN(:recipe_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);

        if (!$stmt->execute()) {
            return false;
        }

        return $this->getArray($stmt);
    }

    private function getRecipeTagsByUserId($userId)
    {
        $sql =
            "SELECT BIN_TO_UUID(rt.id, 1) AS id, BIN_TO_UUID(rt.recipe_id, 1) AS recipe_id, BIN_TO_UUID(rt.tag_id, 1) AS tag_id
            FROM ab_recipe r
            INNER JOIN ab_recipe_tag rt ON r.id = rt.recipe_id
            INNER JOIN ab_recipe_user u ON r.id = u.recipe_id
            WHERE u.user_id = UUID_TO_BIN(:user_id, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $this->getArray($stmt);
    }

    public function userExists($username)
    {
        $sql = "SELECT count(1) AS userCount FROM ab_user WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":username", $username);
        $stmt->execute();
        $data = $stmt->fetch();

        return $data["userCount"] >= 1;
    }

    public function recipeUserExists($recipeId, $userId)
    {
        $sql = "SELECT count(1) AS userCount FROM ab_recipe_user WHERE recipe_id = UUID_TO_BIN(:recipe_id, 1) AND user_id = UUID_TO_BIN(:user_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);
        $stmt->bindValue(":user_id", $userId);
        $stmt->execute();
        $data = $stmt->fetch();

        return $data["userCount"] == 1;
    }

    public function recipeTagExists($recipeId, $tagId)
    {
        $sql = "SELECT count(1) AS tagCount FROM ab_recipe_tag WHERE recipe_id = UUID_TO_BIN(:recipe_id, 1) AND tag_id = UUID_TO_BIN(:tag_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);
        $stmt->bindValue(":tag_id", $tagId);
        $stmt->execute();
        $data = $stmt->fetch();

        return $data["tagCount"] == 1;
    }

    private function uidExists($uid)
    {
        $sql = "SELECT count(1) AS userCount FROM ab_oauth WHERE uid = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":uid", $uid);
        $stmt->execute();
        $data = $stmt->fetch();

        return $data["userCount"] >= 1;
    }

    /**
     * @param $username
     * @return User
     */
    public function getUser($username)
    {
        $sql = "SELECT
            BIN_TO_UUID(usr.id, 1) AS id,
            usr.username,
            usr.password_hash,
            usr.family_name,
            usr.given_name,
            oauth.oauth_provider,
            oauth.uid
          FROM
            ab_user usr
            LEFT OUTER JOIN ab_oauth oauth ON usr.id = oauth.user_id
          WHERE
            username = :username";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $data = $stmt->fetch();

        return User::fromArray($data);
    }

    public function updateUser(User $user)
    {
        $sql = "UPDATE ab_user
          SET
            password_hash = :password_hash, 
            given_name = :given_name, 
            family_name = :family_name 
          WHERE
            username = :username";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":password_hash", $user->getPasswordHash());
        $stmt->bindValue(":given_name", $user->getGivenName());
        $stmt->bindValue(":family_name", $user->getFamilyName());
        $stmt->bindValue(":username", $user->getUsername());

        if (!$stmt->execute()) return false;

        if ($this->uidExists($user->getUid())) {
            $sql = "UPDATE ab_oauth SET oauth_provider = :oauth_provider, user_id = UUID_TO_BIN(:user_id, 1) WHERE uid = :uid";
        } else {
            $sql = "INSERT INTO ab_oauth (id, oauth_provider, uid, user_id) VALUES (UUID_TO_BIN(UUID(), 1), :oauth_provider, :uid, UUID_TO_BIN(:user_id, 1))";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":oauth_provider", $user->getOauthProvider());
        $stmt->bindValue(":user_id", $user->getId());
        $stmt->bindValue(":uid", $user->getUid());

        return $stmt->execute();
    }

    public function createUser(User $user)
    {
        if (!$uuid = $this->newUUID()) {
            return false;
        }

        $sql = "INSERT INTO ab_user (id, username, password_hash, given_name, family_name) VALUES (UUID_TO_BIN(:id, 1), :username, :password_hash, :given_name, :family_name)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $uuid);
        $stmt->bindValue(":username", $user->getUsername());
        $stmt->bindValue(":password_hash", $user->getPasswordHash());
        $stmt->bindValue(":given_name", $user->getGivenName());
        $stmt->bindValue(":family_name", $user->getFamilyName());

        if (!$stmt->execute()) return false;

        $user_id = $uuid;

        $sql = "INSERT INTO ab_oauth (id, oauth_provider, uid, user_id) VALUES (UUID_TO_BIN(UUID(), 1), :oauth_provider, :uid, UUID_TO_BIN(:user_id, 1))";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":oauth_provider", $user->getOauthProvider());
        $stmt->bindValue(":uid", $user->getUid());
        $stmt->bindValue(":user_id", $user_id);

        return $stmt->execute();
    }

    public function fetchToken($header)
    {
        $regexp = "/Bearer\s+(.*)$/i";
        if (preg_match($regexp, $header, $matches)) {
            return $matches[1];
        }
        return "";
    }

    public function verifyOAuthUser($username, $uid)
    {
        $sql = "SELECT
            BIN_TO_UUID(usr.id, 1) AS user_id
          FROM
            ab_user usr
            INNER JOIN ab_oauth oauth ON usr.id = oauth.user_id
          WHERE
            usr.username = :username
            AND oauth.uid = :uid";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":uid", $uid);

        if (!$stmt->execute()) {
            return false;
        }

        $data = $stmt->fetch();

        return $data["user_id"];
    }

    public function insertShoppingListItem($shoppingListItem)
    {
        // check if the recipe id is empty or belongs to this user
        if (!$this->isInvalidId($shoppingListItem->recipe_id)) {

            // if no recipe found for this user, set recipe_id to null
            if (!$this->hasUserAccessToRecipe($shoppingListItem->recipe_id)) {
                $shoppingListItem->recipe_id = null;
            }
        }

        if (!$uuid = $this->newUUID()) {
            return false;
        }

        $sql = "INSERT INTO ab_shopping_list_item (id, recipe_id, ingredient_id, name, quantity, unit_id, sort_order)
          VALUES (UUID_TO_BIN(:id, 1), UUID_TO_BIN(:recipe_id, 1), UUID_TO_BIN(:ingredient_id, 1), :name, :quantity, UUID_TO_BIN(:unit_id, 1), :sort_order)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $uuid);
        $stmt->bindValue(":recipe_id", $shoppingListItem->recipe_id);
        $stmt->bindValue(":ingredient_id", $shoppingListItem->ingredient_id);
        $stmt->bindValue(":name", $shoppingListItem->name);
        $stmt->bindValue(":quantity", $shoppingListItem->quantity);
        $stmt->bindValue(":unit_id", $shoppingListItem->unit_id);
        $stmt->bindValue(":sort_order", $shoppingListItem->sort_order);
        $stmt->execute();
        $data = $stmt->fetch();

        if ($data === false) {
            return false;
        }

        return $uuid;
    }

    private function insertRecipe(Recipe $recipe)
    {
        if (!$uuid = $this->newUUID()) {
            return false;
        }

        $sql = "INSERT INTO ab_recipe (id, name, portions, duration, url) VALUES (UUID_TO_BIN(:id, 1), :name, :portions, :duration, :url)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $uuid);
        $stmt->bindValue(":name", $recipe->getName());
        $stmt->bindValue(":portions", $recipe->getPortions());
        $stmt->bindValue(":duration", $recipe->getDuration());
        $stmt->bindValue(":url", $recipe->getUrl());

        if (!$stmt->execute()) {
            return false;
        }

        return $uuid;
    }

    private function insertRecipeStep($recipeId, $description, $isSection, $sortOrder)
    {
        $sql =
            "INSERT INTO ab_recipe_step (id, recipe_id, description, is_section, sort_order) " .
            "VALUES (UUID_TO_BIN(UUID(), 1), UUID_TO_BIN(:recipe_id, 1), :description, :is_section, :sort_order)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);
        $stmt->bindValue(":description", $description);
        $stmt->bindValue(":is_section", $isSection);
        $stmt->bindValue(":sort_order", $sortOrder);

        return $stmt->execute();
    }

    public function addRecipe($input)
    {
        $error = false;

        if (empty($input['recipename'])) {
            $this->error[] = "Recipe name cannot be empty";
            $error = true;
        }
        if (empty($input['servings'])) {
            $this->error[] = "Servings cannot be empty";
            $error = true;
        }
        if (empty($input['duration'])) {
            $this->error[] = "Duration cannot be empty";
            $error = true;
        }
        if (!is_numeric($input['servings'])) {
            $this->error[] = "Servings must be a number";
            $error = true;
        }
        if (!is_numeric($input['duration'])) {
            $this->error[] = "Duration must be in minutes";
            $error = true;
        }
        if (empty($input['users'])) {
            $this->error[] = "Please select at least one user";
            $error = true;
        }

        if (!$error) {

            $recipe = new Recipe(null, $input['recipename'], $input['servings'], $input['duration'] * 60, $input['url']);
            $recipeId = $this->insertRecipe($recipe);

            if (!$recipeId) {
                $this->error[] = "Error inserting recipe";
                return false;
            }

            $recipe->setId($recipeId);

            $ingredients = $this->parseIngredients($input['ingredients']);

            $sortOrder = 1;
            foreach ($ingredients as $ingredient) {
                $quantity = empty($input['servings']) || empty($ingredient['quantity']) ? $ingredient['quantity'] : $ingredient['quantity'] / $input['servings'];
                $recipeIngredient = new RecipeIngredient(null, $recipeId, $ingredient['ingredient_id'], $quantity, $ingredient['unit_id'], $sortOrder);

                if (!$this->insertRecipeIngredient($recipeIngredient)) {
                    $this->error[] = "Error inserting ingredient " . $ingredient;
                    return false;
                }

                $sortOrder++;
            }

            $recipe->setIngredients($this->getRecipeIngredientsByRecipeId($recipeId));

            $steps = $this->parseSteps($input['steps']);

            $sortOrder = 1;
            foreach ($steps as $step) {
                if (!$this->insertRecipeStep($recipeId, $step['description'], $step['is_section'], $sortOrder)) {
                    $this->error[] = "Error inserting step " . $step;
                    return false;
                }
                $sortOrder++;
            }

            $recipe->setSteps($this->getRecipeStepsByRecipeId($recipeId));

            $this->insertRecipeUsers($recipeId, $input['users']);
            $recipe->setUsers($this->getRecipeUsersByRecipeId($recipeId));

            $this->insertRecipeTags($recipeId, $input['tags']);
            $recipe->setTags($this->getRecipeTagsByRecipeId($recipeId));

            return $recipe;
        }

        return false;
    }

    public function deleteRecipe($id)
    {
        if (!$this->deleteRecipeUserByRecipeId($id)) {
            return false;
        }

        if (!$this->deleteRecipeTagByRecipeId($id)) {
            return false;
        }

        if (!$this->deleteRecipeStepByRecipeId($id)) {
            return false;
        }

        if (!$this->deleteRecipeIngredientByRecipeId($id)) {
            return false;
        }

        $sql = "DELETE FROM ab_recipe WHERE id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id);

        return $stmt->execute();
    }

    private function deleteRecipeUserByRecipeId($id)
    {
        $sql = "DELETE FROM ab_recipe_user WHERE recipe_id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id);

        return $stmt->execute();
    }

    private function deleteRecipeUser($recipeId, $userId)
    {
        $sql = "DELETE FROM ab_recipe_user WHERE recipe_id = UUID_TO_BIN(:recipe_id, 1) AND user_id = UUID_TO_BIN(:user_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);
        $stmt->bindValue(":user_id", $userId);

        return $stmt->execute();
    }

    private function deleteRecipeTag($recipeId, $tagId)
    {
        $sql = "DELETE FROM ab_recipe_tag WHERE recipe_id = UUID_TO_BIN(:recipe_id, 1) AND tag_id = UUID_TO_BIN(:tag_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);
        $stmt->bindValue(":tag_id", $tagId);

        return $stmt->execute();
    }

    private function deleteRecipeTagByRecipeId($id)
    {
        $sql = "DELETE FROM ab_recipe_tag WHERE recipe_id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id);

        return $stmt->execute();
    }

    private function deleteRecipeStepByRecipeId($id)
    {
        $sql = "DELETE FROM ab_recipe_step WHERE recipe_id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id);

        return $stmt->execute();
    }

    private function deleteRecipeIngredientByRecipeId($id)
    {
        $sql = "DELETE FROM ab_recipe_ingredient WHERE recipe_id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id);

        return $stmt->execute();
    }

    public function getErrors()
    {
        return $this->error;
    }


    public function editRecipe($input)
    {
        $error = false;

        if (empty($input['recipename'])) {
            $this->error[] = "Recipe name cannot be empty";
            $error = true;
        }
        if (empty($input['servings'])) {
            $this->error[] = "Servings cannot be empty";
            $error = true;
        }
        if (empty($input['duration'])) {
            $this->error[] = "Duration cannot be empty";
            $error = true;
        }
        if (!is_numeric($input['servings'])) {
            $this->error[] = "Servings must be a number";
            $error = true;
        }
        if (!is_numeric($input['duration'])) {
            $this->error[] = "Duration must be in minutes";
            $error = true;
        }
        if (empty($input['users'])) {
            $this->error[] = "Please select at least one user";
            $error = true;
        }

        if (!$error) {
            $recipeId = $input['id'];
            $recipe = new Recipe($recipeId, $input['recipename'], $input['servings'], $input['duration'] * 60, $input['url']);

            if (!$this->updateRecipe($recipe)) {
                $this->error[] = "Could not update recipe.";
                return false;
            }

            $this->deleteRecipeIngredientByRecipeId($recipeId);

            $ingredients = $this->parseIngredients($input['ingredients']);

            $sortOrder = 1;
            foreach ($ingredients as $ingredient) {
                $quantity = empty($input['servings']) || empty($ingredient['quantity']) ? $ingredient['quantity'] : $ingredient['quantity'] / $input['servings'];
                $recipeIngredient = new RecipeIngredient(null, $recipeId, $ingredient['ingredient_id'], $quantity, $ingredient['unit_id'], $sortOrder);

                if (!$this->insertRecipeIngredient($recipeIngredient)) {
                    $this->error[] = "Error inserting ingredient " . $ingredient;
                    return false;
                }

                $sortOrder++;
            }

            $recipe->setIngredients($this->getRecipeIngredientsByRecipeId($recipeId));

            $this->deleteRecipeStepByRecipeId($recipeId);

            $steps = $this->parseSteps($input['steps']);

            $sortOrder = 1;
            $parsedSteps = array();
            foreach ($steps as $step) {
                if (!$this->insertRecipeStep($recipeId, $step['description'], $step['is_section'], $sortOrder)) {
                    $this->error[] = "Error inserting step " . $step;
                    return false;
                }
                $sortOrder++;
                $parsedSteps[] = $step['description'];
            }

            $recipe->setSteps($parsedSteps);

            $this->updateRecipeUsers($recipeId, $input['users']);
            $recipe->setUsers($this->getRecipeUsersByRecipeId($recipeId));

            $this->updateRecipeTags($recipeId, $input['tags']);
            $recipe->setTags($this->getRecipeTagsByRecipeId($recipeId));

            return $recipe;
        }

        return false;
    }

    private function insertRecipeUsers($recipeId, $userIds)
    {
        foreach ($userIds as $userId) {
            if (!$this->insertRecipeUser($recipeId, $userId)) {
                return false;
            }
        }

        return true;
    }

    private function insertRecipeTags($recipeId, $tagIds)
    {
        foreach ($tagIds as $tagId) {
            if (!$this->insertRecipeTag($recipeId, $tagId)) {
                return false;
            }
        }

        return true;
    }

    private function updateRecipeUsers($recipeId, $userIds)
    {
        foreach ($userIds as $userId) {
            if (!$this->recipeUserExists($recipeId, $userId) && !$this->insertRecipeUser($recipeId, $userId)) {
                return false;
            }
        }

        $users = $this->getRecipeUsersByRecipeId($recipeId);
        foreach ($users as $user) {
            if (!in_array($user['id'], $userIds)) {
                $this->deleteRecipeUser($recipeId, $user['id']);
            }
        }

        return true;
    }

    private function updateRecipeTags($recipeId, $tagIds)
    {
        foreach ($tagIds as $tagId) {
            if (!$this->recipeTagExists($recipeId, $tagId) && !$this->insertRecipeTag($recipeId, $tagId)) {
                return false;
            }
        }

        $tags = $this->getRecipeTagsByRecipeId($recipeId);
        foreach ($tags as $tag) {
            if (!in_array($tag['id'], $tagIds)) {
                $this->deleteRecipeTag($recipeId, $tag['id']);
            }
        }

        return true;
    }

    private function insertRecipeUser($recipeId, $userId)
    {
        $sql = "INSERT INTO ab_recipe_user (id, recipe_id, user_id) VALUES (UUID_TO_BIN(UUID(), 1), UUID_TO_BIN(:recipe_id, 1), UUID_TO_BIN(:user_id, 1))";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);
        $stmt->bindValue(":user_id", $userId);

        return $stmt->execute();
    }

    private function insertRecipeTag($recipeId, $tagId)
    {
        $sql = "INSERT INTO ab_recipe_tag (id, recipe_id, tag_id) VALUES (UUID_TO_BIN(UUID(), 1), UUID_TO_BIN(:recipe_id, 1), UUID_TO_BIN(:tag_id, 1))";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);
        $stmt->bindValue(":tag_id", $tagId);

        return $stmt->execute();
    }

    private function parseIngredients($ingredients)
    {
        if (empty($ingredients)) {
            return array();
        }

        $lines = explode("\n", $ingredients);
        $units = $this->getUnitNames();
        $units_regex = $units ? implode("|", $units) : "[a-zäöü]+";
        $pattern1 = "/^([0-9]+(?:(?:.|,)[0-9]+)?) ?(" . $units_regex . ") (.+)$/i";
        $pattern2 = "/^([0-9]+(?:(?:.|,)[0-9]+)?) (.+)$/i";

        $parsed = array();
        $i = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match($pattern1, $line, $matches)) {
                $parsed[$i]['quantity'] = str_replace(",", ".", $matches[1]);
                $parsed[$i]['unit'] = $matches[2];
                $parsed[$i]['unit_id'] = $this->getUnitIdByName($parsed[$i]['unit']);
                $parsed[$i]['ingredient'] = $matches[3];
                $parsed[$i]['ingredient_id'] = $this->getIngredientIdByName($parsed[$i]['ingredient'], true);
                $parsed[$i]['parsed'] = $matches[1] . " " . $matches[2] . " " . $matches[3];
            } else if (preg_match($pattern2, $line, $matches)) {
                $parsed[$i]['quantity'] = str_replace(",", ".", $matches[1]);
                $parsed[$i]['unit'] = null;
                $parsed[$i]['unit_id'] = null;
                $parsed[$i]['ingredient'] = $matches[2];
                $parsed[$i]['ingredient_id'] = $this->getIngredientIdByName($parsed[$i]['ingredient'], true);
                $parsed[$i]['parsed'] = $matches[1] . " " . $matches[2];
            } else {
                $parsed[$i]['quantity'] = null;
                $parsed[$i]['unit'] = null;
                $parsed[$i]['unit_id'] = null;
                $parsed[$i]['ingredient'] = $line;
                $parsed[$i]['ingredient_id'] = $this->getIngredientIdByName($parsed[$i]['ingredient'], true);
                $parsed[$i]['parsed'] = $line;
            }
            $i++;
        }

        return $parsed;
    }

    private function parseSteps($steps)
    {
        if (empty($steps)) {
            return array();
        }

        $steps = trim($steps);
        $steps = preg_replace("/\r\n/m", "\n", $steps); // replace windows line break by \n
        $steps = preg_replace("/\r/m", "\n", $steps); // replace mac line break by \n
        $steps = preg_replace("/\t/m", " ", $steps); // replace tabs by spaces
        $steps = preg_replace("/^[0-9\-\. ]+/m", "", $steps); // remove numbering (first line, $steps)
        $steps = preg_replace("/\n[0-9\-\. ]+/m", "\n", $steps); // remove numbering (other lines, $steps)
        $steps = preg_replace("/ +\n/m", "\n", $steps); // trim end of each line
        $steps = preg_replace("/\n +/m", "\n", $steps); // trim beginning of each line
        $steps = preg_replace("/\n\n+/m", "\n", $steps); // remove multiple empty lines
        $steps = preg_replace("/\n/m", ".\n", $steps); // add a dot at the end of each line
        $steps = preg_replace("/$/m", ".", $steps); // add a dot at the end of the last line
        $steps = preg_replace("/\.\.+/m", ".", $steps); // remove multiple dots created by the previous step
        $steps = preg_replace("/\. */m", ".\n", $steps); // enter a break line after each dot
        $steps = preg_replace("/:\. */m", ":", $steps); // remove dots at the end of lines which end with a colon (:, $steps)
        $steps = preg_replace("/\n\n+/m", "\n", $steps); // remove multiple empty lines
        $steps = preg_replace("/\n+$/m", "", $steps); // remove the last line break
        $steps = explode("\n", $steps);

        $parsed = array();
        $i = 0;
        foreach ($steps as $step) {
            $parsed[$i]['description'] = $step;
            $parsed[$i]['is_section'] = substr($step, -1) == ":" ? 1 : 0;
            $i++;
        }

        return $parsed;
    }

    private function getIngredientIdByName($ingredientName, $insertIfNotExist = false)
    {
        $sql = "SELECT BIN_TO_UUID(id, 1) AS id FROM ab_ingredient WHERE name = :ingredient_name LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":ingredient_name", $ingredientName);

        if (!$stmt->execute()) {
            return false;
        }

        $data = $stmt->fetch();

        if (empty($data) && $insertIfNotExist) {

            if (!$uuid = $this->newUUID()) {
                return false;
            }

            $sql = "INSERT INTO ab_ingredient (id, name) VALUES (UUID_TO_BIN(:id, 1), :ingredient_name);";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id", $uuid);
            $stmt->bindValue(":ingredient_name", $ingredientName);

            if (!$stmt->execute()) {
                return false;
            }

            $ingredientId = $uuid;

            return $ingredientId;
        }

        return $data['id'];
    }

    private function getUnitNames()
    {
        $sql = "SELECT name_singular AS unit_name FROM ab_unit UNION SELECT name_plural FROM ab_unit";
        $stmt = $this->db->prepare($sql);

        if (!$stmt->execute()) {
            return false;
        }

        $data = array();
        while ($row = $stmt->fetch()) {
            $data[] = $row['unit_name'];
        }

        return $data;
    }

    private function getUnitIdByName($unitName)
    {
        $sql = "SELECT BIN_TO_UUID(id, 1) AS id FROM ab_unit WHERE name_singular = :unit_name OR name_plural = :unit_name LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":unit_name", $unitName);

        if (!$stmt->execute()) {
            return false;
        }

        $data = $stmt->fetch();

        return $data['id'];
    }

    private function insertRecipeIngredient(RecipeIngredient $ingredient)
    {
        if (!$uuid = $this->newUUID()) {
            return false;
        }

        $sql =
            "INSERT INTO ab_recipe_ingredient (id, recipe_id, ingredient_id, quantity, unit_id, sort_order) " .
            "VALUES (UUID_TO_BIN(:id, 1), UUID_TO_BIN(:recipe_id, 1), UUID_TO_BIN(:ingredient_id, 1), :quantity, UUID_TO_BIN(:unit_id, 1), :sort_order)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $uuid);
        $stmt->bindValue(":recipe_id", $ingredient->getRecipeId());
        $stmt->bindValue(":ingredient_id", $ingredient->getIngredientId());
        $stmt->bindValue(":quantity", $ingredient->getQuantity());
        $stmt->bindValue(":unit_id", $ingredient->getUnitId());
        $stmt->bindValue(":sort_order", $ingredient->getSortOrder());

        if (!$stmt->execute()) {
            return false;
        }

        return $uuid;
    }

    public function updateRecipe(Recipe $recipe)
    {
        $sql = "UPDATE ab_recipe SET name = :name, portions = :portions, duration = :duration, url = :url WHERE id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":name", $recipe->getName());
        $stmt->bindValue(":portions", $recipe->getPortions());
        $stmt->bindValue(":duration", $recipe->getDuration());
        $stmt->bindValue(":url", $recipe->getUrl());
        $stmt->bindValue(":id", $recipe->getId());

        return $stmt->execute();
    }

    public function updateRecipeIngredient(RecipeIngredient $recipeIngredient)
    {
        if (!$this->hasUserAccessToRecipe($recipeIngredient->getRecipeId())) {
            return false;
        }
        $sql = "UPDATE ab_recipe_ingredient SET recipe_id = UUID_TO_BIN(:recipe_id, 1), ingredient_id = UUID_TO_BIN(:ingredient_id, 1), quantity = :quantity, unit_id = UUID_TO_BIN(:unit_id, 1), sort_order = :sort_order WHERE id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeIngredient->getRecipeId());
        $stmt->bindValue(":ingredient_id", $recipeIngredient->getIngredientId());
        $stmt->bindValue(":quantity", $recipeIngredient->getQuantity());
        $stmt->bindValue(":unit_id", $recipeIngredient->getUnitId());
        $stmt->bindValue(":sort_order", $recipeIngredient->getSortOrder());
        $stmt->bindValue(":id", $recipeIngredient->getId());

        return $stmt->execute();
    }

    public function updateRecipeStep(RecipeStep $recipeStep)
    {
        if (!$this->hasUserAccessToRecipe($recipeStep->getRecipeId())) {
            return false;
        }
        $sql = "UPDATE ab_recipe_step SET recipe_id = UUID_TO_BIN(:recipe_id, 1), description = :description, is_section = :is_section, sort_order = :sort_order WHERE id = UUID_TO_BIN(:id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeStep->getRecipeId());
        $stmt->bindValue(":description", $recipeStep->getDescription());
        $stmt->bindValue(":is_section", $recipeStep->getIsSection());
        $stmt->bindValue(":sort_order", $recipeStep->getSortOrder());
        $stmt->bindValue(":id", $recipeStep->getId());

        return $stmt->execute();
    }

    private function hasUserAccessToRecipe($recipeId)
    {
        if ($this->isInvalidId($this->userId) || $this->isInvalidId($recipeId)) {
            return false;
        }

        $sql = "SELECT 1 AS allowed FROM ab_recipe_user WHERE recipe_id = UUID_TO_BIN(:recipe_id, 1) AND user_id = UUID_TO_BIN(:user_id, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":recipe_id", $recipeId);
        $stmt->bindValue(":user_id", $this->userId);

        if (!$stmt->execute()) {
            return false;
        }

        $data = $stmt->fetch();

        return $data["allowed"] == 1;

    }

    private function isInvalidId($id)
    {
        return $id == null;
    }

    private function newUUID()
    {
        try {
            return Uuid::uuid1();
        } catch (\Exception $e) {
            return false;
        }
    }
}
