<?php
/**
 * Author: Yeray García Quintana
 * Date: 29.07.2018
 */

use Recipes\db\Database;
use Recipes\model\Recipe;
use Recipes\model\RecipeService;

require_once 'header.inc.php';

if ($_SESSION['loggedIn'] != 1) {
    header('Location: login.php');
    die();
}

require_once '../../vendor/autoload.php';

$recipeService = new RecipeService(Database::getInstance());

if (isset($_POST['save-recipe']) && empty($_POST['id'])) {
    try {
        $result = $recipeService->addRecipe($_POST);
    } catch (PDOException $e) {
        die($e->getMessage());
    }
    $recipe = $result ? $result : Recipe::fromPost($_POST);
    if ($result) {
        header("Location: " . basename(__FILE__) . "?id=" . $result->getId());
        die();
    }
} else if (isset($_POST['save-recipe']) && !empty($_POST['id'])) {
    try {
        $result = $recipeService->editRecipe($_POST);
    } catch (Exception $e) {
        die($e->getMessage());
    }
    $recipe = $result ? $result : Recipe::fromPost($_POST);
} else if (isset($_POST['delete-recipe']) && !empty($_POST['id'])) {
    $recipe = Recipe::getEmptyRecipe();
    if ($recipeService->deleteRecipe($_POST['id'])) {
        header("Location: " . basename(__FILE__));
        die();
    }
} else if (!empty($_GET['id'])) {
    $recipe = Recipe::fromArray(
        $recipeService->getRecipeById($_GET['id']),
        $recipeService->getRecipeIngredientsByRecipeId($_GET['id']),
        $recipeService->getRecipeStepsByRecipeId($_GET['id']),
        $recipeService->getRecipeUsersByRecipeId($_GET['id']),
        $recipeService->getRecipeTagsByRecipeId($_GET['id'])
    );
} else {
    $recipe = Recipe::getEmptyRecipe();
}

?>
<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">

    <title>Add or edit a recipe</title>
    <meta name="description" content="Add recipe">
    <meta name="author" content="yeraygarcia.com">

    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

<div id="header">
    <a href="?logout">Sign out</a>
</div>

<div id="content">

    <div id="info-box">
        <?php
        if (!empty($recipeService->getErrors())) {
            print '<ul>';
            foreach ($recipeService->getErrors() as $error) {
                print '<li>' . $error . '</li>';
            }
            print '</ul>';
        }
        ?>
    </div>

    <div id="recipe-picker">
        <form action="index.php" method="get">
            <div class="flex-container">
                <p>
                    <label for="recipes">Select a recipe:</label>
                    <select id="recipes" name="id" title="Select a recipe"
                            onchange="document.location = '?id=' + this.value">
                        <option>--</option>
                        <?php
                        $recipes = $recipeService->getRecipesByUserId($_SESSION['userId']);
                        foreach ($recipes as $currentRecipe) {
                            $checked = $currentRecipe['id'] == $recipe->getId() ? "selected='selected'" : "";
                            print '
                        <option value="' . $currentRecipe['id'] . '" ' . $checked . '>' . $currentRecipe['name'] . '</option>';
                        }
                        ?>
                    </select>
                    or add a <a href="index.php">new recipe</a>.
                </p>
            </div>
        </form>
    </div>

    <div id="recipe-form">
        <form action="index.php?id=<?= $recipe->getId(); ?>" method="post">
            <div id="recipe" class="flex-container">
                <p>
                    <input id="id" name="id" type="hidden" value="<?= $recipe->getId() ?>"/>
                    <label for="recipename">Recipe name*:</label>
                    <input id="recipename" name="recipename" type="text" value="<?= $recipe->getName() ?>"/>
                </p>

                <p>
                    <label for="servings">Servings*:</label>
                    <input id="servings" name="servings" type="number" value="<?= $recipe->getPortions() ?>"/>
                </p>
                <?php if (true || empty($recipe->getIngredientsAsString()) || empty($recipe->getId())) { ?>
                    <p>
                        <label for="ingredients">Ingredients:</label>
                        <textarea id="ingredients"
                                  name="ingredients"><?= $recipe->getIngredientsAsString() ?></textarea>
                    </p>
                <?php } else { ?>
                    <p>
                        <label for="ingredients">Ingredients:</label>
                        <?php
                        $ingredients = $recipe->getIngredients();
                        foreach ($ingredients as $ingredient) {
                            print '
                            <input name="ingredients[]" value="' . $ingredient . '" /><br/>';
                        }
                        ?>
                    </p>
                <?php } ?>

                <p>
                    <label for="steps">Steps:</label>
                    <textarea id="steps" name="steps"><?= $recipe->getStepsAsString() ?></textarea>
                </p>

                <p>
                    <label for="duration">Duration (min)*:</label>
                    <input id="duration" name="duration" type="number" value="<?= $recipe->getDurationInMinutes() ?>"/>
                </p>

                <p>
                    <label for="url">Quelle:</label>
                    <input id="url" name="url" type="text" value="<?= $recipe->getUrl() ?>"/>
                </p>

                <p class="checkbox-container">
                    <span class="label">Tags:</span>
                    <?php
                    $tags = $recipeService->getTags();
                    foreach ($tags as $tag) {
                        $checked = !empty($recipe->getTags()) && in_array($tag, $recipe->getTags()) ? "checked='checked'" : "";
                        print '
                        <span class="tag-container">
                            <input id="tag' . $tag['id'] . '" type="checkbox" name="tags[]" value="' . $tag['id'] . '" ' . $checked . '>
                            <label class="checkbox-label" for="tag' . $tag['id'] . '">' . $tag['name'] . '</label>
                        </span>';
                    }
                    ?>
                </p>

                <p class="checkbox-container">
                    <span class="label">Users:</span>
                    <?php
                    $users = $recipeService->getUsers();
                    foreach ($users as $user) {
                        $checked = !empty($recipe->getUsers()) && in_array($user, $recipe->getUsers()) ? "checked='checked'" : "";
                        print '
                        <input id="user' . $user['id'] . '" type="checkbox" name="users[]" value="' . $user['id'] . '" ' . $checked . '>
                        <label class="checkbox-label" for="user' . $user['id'] . '">' . $user['given_name'] . '</label>';
                    }
                    ?>
                </p>

                <p>
                    <input type="submit" name="save-recipe" value="Save">
                </p>

                <p>
                    <input type="submit" name="delete-recipe" value="Delete">
                </p>
            </div>
        </form>
    </div>

</div>

</body>
</html>
