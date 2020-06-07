<?php
/**
 * Author: Yeray García Quintana
 * Date: 01.11.2018
 */

use PHPUnit\Framework\TestCase;
use Recipes\config\Config;
use Recipes\db\entity\Aisle;
use Recipes\db\entity\Ingredient;
use Recipes\db\entity\Recipe;
use Recipes\db\entity\RecipeIngredient;
use Recipes\db\entity\RecipeStep;
use Recipes\db\entity\RecipeTag;
use Recipes\db\entity\RecipeUser;
use Recipes\db\entity\Tag;
use Recipes\db\entity\Unit;
use Recipes\db\entity\User;
use Recipes\model\RecipeRepository;
use Recipes\model\SyncDto;
use Recipes\model\SyncService;
use Test\config\DebugConfig;
use Test\util\TestHelper;

class SyncServiceTest extends TestCase
{
    /** @var SyncService */
    private $sut;
    /** @var PDO */
    private $db;
    /** @var RecipeRepository */
    private $recipeRepository;

    /**
     * @throws Exception
     */
    protected function setUp()
    {
        $this->db = $this->initDatabase();
        $this->assertNotNull($this->db);
        TestHelper::importSqlFile($this->db, '../db/dropDDL.sql');
        TestHelper::importSqlFile($this->db, '../db/createDDL.sql', Config::TABLE_PREFIX);

        $this->recipeRepository = new RecipeRepository($this->db);
        $this->sut = new SyncService($this->recipeRepository);
    }

    private function initDatabase()
    {
        try {
            $pdo = new PDO(
                "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::TESTDB_NAME . "; charset=UTF8",
                DebugConfig::DB_USER,
                DebugConfig::DB_PASS
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    public function testSyncData_forValidData_ShouldReturnSyncData()
    {
        $aisle = new Aisle("TestAisle");
        $this->recipeRepository->getAisleDao()->insert($aisle);

        $serverIngredient = new Ingredient("serverIngredient", $aisle->getId());
        $deletedIngredient = new Ingredient("deletedIngredient", $aisle->getId());
        $this->recipeRepository->getIngredientDao()->insert($serverIngredient);
        $this->recipeRepository->getIngredientDao()->insert($deletedIngredient);

        $clientSyncData = (new SyncDto())
            ->setLastUpdate("1900-01-01 00:00:00")
            ->setIngredients(array(new Ingredient("newClientIngredient", $aisle->getId())))
            ->setDeletedIngredients(array($deletedIngredient->getId()));

        print json_encode($clientSyncData);

        $user = new User("username");
        $this->sut->setUserId($user->getId());

        $serverSyncData = $this->sut->syncData($clientSyncData);

        $this->assertThat(count($serverSyncData->getAisles()), $this->equalTo(1));

        $ingredients = $serverSyncData->getIngredients();
        $this->assertThat(count($ingredients), $this->equalTo(2));
        $this->assertThat($ingredients[0]->getName(), $this->equalTo("serverIngredient"));
        $this->assertThat($ingredients[1]->getName(), $this->equalTo("newClientIngredient"));

        $deletedIngredients = $serverSyncData->getDeletedIngredients();
        $this->assertThat(count($deletedIngredients), $this->equalTo(1));
        $this->assertThat($deletedIngredients[0], $this->equalTo($deletedIngredient->getId()));
    }

    /**
     * @throws Exception
     */
    public function testSyncData_forDeleteOnClient_ShouldDeleteOnServer()
    {
        $user = new User("Yeray");
        $aisle = new Aisle("Gemüsse");
        $unit = new Unit("Stange", "Stangen");
        $tag = new Tag("veggie");
        $ingredient = new Ingredient("Lauch", $aisle->getId());
        $recipe = new Recipe("Lauchsuppe", 4, 30, "youtube.com/lauchsuppe");
        $recipeIngredient = new RecipeIngredient($recipe->getId(), $ingredient->getId(), 1, $unit->getId());
        $recipeStep = new RecipeStep($recipe->getId(), "Lauch schneiden.", 0, 1);
        $recipeTag = new RecipeTag($recipe->getId(), $tag->getId());
        $recipeUser = new RecipeUser($recipe->getId(), $user->getId());

        $rep = $this->recipeRepository;

        $rep->getUserDao()->insert($user);
        $rep->getAisleDao()->insert($aisle);
        $rep->getUnitDao()->insert($unit);
        $rep->getTagDao()->insert($tag);
        $rep->getIngredientDao()->insert($ingredient);
        $rep->getRecipeDao()->insert($recipe);
        $rep->getRecipeIngredientDao()->insert($recipeIngredient);
        $rep->getRecipeStepDao()->insert($recipeStep);
        $rep->getRecipeTagDao()->insert($recipeTag);
        $rep->getRecipeUserDao()->insert($recipeUser);

        $serverLastUpdate = $rep->getSyncDao()->getLastUpdate();

        // client and server are synchronized (same last update value) and then the client deletes a record
        $clientLastUpdate = $serverLastUpdate;
        $clientSyncData = (new SyncDto())
            ->setLastUpdate($clientLastUpdate)
            ->setDeletedRecipeTags(array($recipeTag->getId()));

        print "\nData from client: " . json_encode($clientSyncData);

        $this->sut->setUserId($user->getId());

        // mock a time delay  after which the client sends a request to the server
        // in order to have a more realistic timestamp for the assertions to work
        sleep(1);

        $serverSyncData = $this->sut->syncData($clientSyncData);

        print "\nData from server: " . json_encode($serverSyncData);

        // Aisles, Units, Tags, Recipes and RecipeSteps are always returned (no modified column yet)
        $this->assertThat(count($serverSyncData->getAisles()), $this->equalTo(1));
        $this->assertThat(count($serverSyncData->getUnits()), $this->equalTo(1));
        $this->assertThat(count($serverSyncData->getTags()), $this->equalTo(1));
        $this->assertThat(count($serverSyncData->getRecipes()), $this->equalTo(1));
        $this->assertThat(count($serverSyncData->getRecipeSteps()), $this->equalTo(1));
        // No Ingredients or RecipeIngredients have changed since last sync
        $this->assertThat(count($serverSyncData->getIngredients()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getRecipeIngredients()), $this->equalTo(0));

        $this->assertThat(count($serverSyncData->getRecipeTags()), $this->equalTo(0));
        $this->assertThat($serverSyncData->getDeletedRecipeTags(), $this->equalTo(array($recipeTag->getId())));
        // Nothing else has been deleted
        $this->assertThat(count($serverSyncData->getDeletedAisles()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedUnits()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedTags()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedRecipes()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedRecipeSteps()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedIngredients()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedRecipeIngredients()), $this->equalTo(0));

        // Check the counts directly from the database
        $this->assertThat(count($rep->getAisleDao()->findAll()), $this->equalTo(1));
        $this->assertThat(count($rep->getUnitDao()->findAll()), $this->equalTo(1));
        $this->assertThat(count($rep->getTagDao()->findAll()), $this->equalTo(1));
        $this->assertThat(count($rep->getRecipeDao()->findAll()), $this->equalTo(1));
        $this->assertThat(count($rep->getRecipeStepDao()->findAll()), $this->equalTo(1));
        // 1 Ingredient and 1 RecipeIngredients in the database
        $this->assertThat(count($rep->getIngredientDao()->findAll()), $this->equalTo(1));
        $this->assertThat(count($rep->getRecipeIngredientDao()->findAll()), $this->equalTo(1));
        // 0 RecipeTags (1 was deleted)
        $this->assertThat(count($rep->getRecipeTagDao()->findAll()), $this->equalTo(0));
        $this->assertThat(count($rep->getDeletedDao()->findAll()), $this->equalTo(1));
    }

    /**
     * @throws Exception
     */
    public function testSyncData_forInsertOnClient_ShouldInsertOnServer()
    {
        $user = new User("Yeray");
        $aisle = new Aisle("Gemüsse");
        $unit = new Unit("Stange", "Stangen");
        $tag = new Tag("veggie");
        $ingredient = new Ingredient("Lauch", $aisle->getId());
        $recipe = new Recipe("Lauchsuppe", 4, 30, "youtube.com/lauchsuppe");
        $recipeIngredient = new RecipeIngredient($recipe->getId(), $ingredient->getId(), 1, $unit->getId());
        $recipeStep = new RecipeStep($recipe->getId(), "Lauch schneiden.", 0, 1);
        $recipeTag = new RecipeTag($recipe->getId(), $tag->getId());
        $recipeUser = new RecipeUser($recipe->getId(), $user->getId());

        $rep = $this->recipeRepository;

        $rep->getUserDao()->insert($user);
        $rep->getAisleDao()->insert($aisle);
        $rep->getUnitDao()->insert($unit);
        $rep->getTagDao()->insert($tag);
        $rep->getIngredientDao()->insert($ingredient);
        $rep->getRecipeDao()->insert($recipe);
        $rep->getRecipeIngredientDao()->insert($recipeIngredient);
        $rep->getRecipeStepDao()->insert($recipeStep);
        $rep->getRecipeTagDao()->insert($recipeTag);
        $rep->getRecipeUserDao()->insert($recipeUser);

        $serverLastUpdate = $rep->getSyncDao()->getLastUpdate();

        // client and server are synchronized (same last update value) and then the client inserts a record
        $clientLastUpdate = $serverLastUpdate;
        $newIngredient = new Ingredient("Knoblauch", $aisle->getId());
        $newRecipeStep = new RecipeStep($recipe->getId(), "Knoblauch schneiden.", 0, 2);
        $clientSyncData = (new SyncDto())
            ->setLastUpdate($clientLastUpdate)
            ->setIngredients(array($newIngredient))
            ->setRecipeSteps(array($newRecipeStep));

        print "\nData from client: " . json_encode($clientSyncData);

        $this->sut->setUserId($user->getId());

        // mock a time delay  after which the client sends a request to the server
        // in order to have a more realistic timestamp for the assertions to work
        sleep(1);

        $serverSyncData = $this->sut->syncData($clientSyncData);

        print "\nData from server: " . json_encode($serverSyncData);

        // Aisles, Units, Tags, Recipes, RecipeTags and RecipeSteps are always returned (no modified column yet)
        $this->assertThat(count($serverSyncData->getAisles()), $this->equalTo(1));
        $this->assertThat(count($serverSyncData->getUnits()), $this->equalTo(1));
        $this->assertThat(count($serverSyncData->getTags()), $this->equalTo(1));
        $this->assertThat(count($serverSyncData->getRecipes()), $this->equalTo(1));
        $this->assertThat(count($serverSyncData->getRecipeTags()), $this->equalTo(1));
        // 2 RecipeSteps instead of 1 (the existing one + the new one)
        $this->assertThat(count($serverSyncData->getRecipeSteps()), $this->equalTo(2));
        // 1 Ingredient and no RecipeIngredients have changed since last sync
        $this->assertThat(count($serverSyncData->getIngredients()), $this->equalTo(1));
        $this->assertThat(count($serverSyncData->getRecipeIngredients()), $this->equalTo(0));

        // Nothing has been deleted
        $this->assertThat(count($serverSyncData->getDeletedAisles()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedUnits()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedTags()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedRecipes()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedRecipeSteps()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedIngredients()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedRecipeIngredients()), $this->equalTo(0));
        $this->assertThat(count($serverSyncData->getDeletedRecipeTags()), $this->equalTo(0));

        // Check the counts directly from the database
        $this->assertThat(count($rep->getAisleDao()->findAll()), $this->equalTo(1));
        $this->assertThat(count($rep->getUnitDao()->findAll()), $this->equalTo(1));
        $this->assertThat(count($rep->getTagDao()->findAll()), $this->equalTo(1));
        $this->assertThat(count($rep->getRecipeDao()->findAll()), $this->equalTo(1));
        $this->assertThat(count($rep->getRecipeTagDao()->findAll()), $this->equalTo(1));
        // 2 RecipeSteps instead of 1 (the existing one + the new one)
        $this->assertThat(count($rep->getRecipeStepDao()->findAll()), $this->equalTo(2));
        // 2 Ingredient and 1 RecipeIngredients in the database
        $this->assertThat(count($rep->getIngredientDao()->findAll()), $this->equalTo(2));
        $this->assertThat(count($rep->getRecipeIngredientDao()->findAll()), $this->equalTo(1));
    }
}
