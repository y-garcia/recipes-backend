<?php

use Monolog\Logger as Logger;
use Recipes\config\Config;
use Recipes\db\Database;
use Recipes\model\Recipe;
use Recipes\model\RecipeIngredient;
use Recipes\model\RecipeRepository;
use Recipes\model\RecipeService;
use Recipes\model\RecipeStep;
use Recipes\model\ResultDto;
use Recipes\model\SyncDto;
use Recipes\model\SyncService;
use Recipes\model\User;
use Slim\Http\Request;
use Slim\Http\Response;

require '../../vendor/autoload.php';

$app = new \Slim\App();

$container = $app->getContainer();

$container['logger'] = function () {
    $logger = new Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function () {
    return Database::getInstance();
};

$container['recipeRepository'] = function ($c) {
    return new RecipeRepository($c['db']);
};

$container['recipeService'] = function ($c) {
    return new RecipeService($c['db']);
};

$container['syncService'] = function ($c) {
    return new SyncService($c['recipeRepository']);
};

$errorHandling = function (Request $request, Response $response, $next) {

    if ($this->db == null) {
        $this->logger->addError(Config::ERROR0001 . ": " . Config::ERROR_MESSAGE_0001);
        $result = new ResultDto(500, Config::ERROR0001, Config::ERROR_MESSAGE_0001, null);
        return $response->withJson($result, 500);
    }

    $response = $next($request, $response);

    $this->db = null;

    return $response;
};

$authenticate = function (Request $request, Response $response, $next) {
    $headerValueArray = $request->getHeader("Authorization");

    $idToken = count($headerValueArray) > 0 ? $this->recipeService->fetchToken($headerValueArray[0]) : null;

    if (empty($idToken)) {
        $this->logger->addError(Config::ERROR0008 . ": " . Config::ERROR_MESSAGE_0008);
        $result = new ResultDto(401, Config::ERROR0008, Config::ERROR_MESSAGE_0008, null);
        return $response->withJson($result, 401);
    }

    $client = new Google_Client(['client_id' => Config::CLIENT_ID]);  // Specify the CLIENT_ID of the app that accesses the backend
    try {
        $payload = $client->verifyIdToken($idToken);
    } catch (Exception $e) {
        $this->logger->addError(Config::ERROR0010 . ": " . Config::ERROR_MESSAGE_0010);
        $result = new ResultDto(401, Config::ERROR0010, $e->getMessage(), null);
        return $response->withJson($result, 401);
    }

    if ($payload) {
        $username = $payload['email'];
        $uid = $payload['sub'];

        if (empty($username) || empty($uid)) {
            $this->logger->addError(Config::ERROR0006 . ": " . Config::ERROR_MESSAGE_0006);
            $result = new ResultDto(400, Config::ERROR0006, Config::ERROR_MESSAGE_0006, null);
            return $response->withJson($result, 400);
        }

        try {
            if ($userId = $this->recipeService->verifyOAuthUser($username, $uid)) {
                $this->recipeService->setUserId($userId);
                $this->syncService->setUserId($userId);
                return $next($request, $response);
            } else {
                $this->logger->addError(Config::ERROR0005 . ": " . Config::ERROR_MESSAGE_0005);
                $this->logger->addError("User $username with uid $uid could not be verified");
                $result = new ResultDto(401, Config::ERROR0005, "User could not be verified", null);
                return $response->withJson($result, 401);
            }
        } catch (PDOException $exception) {
            $this->logger->addError(Config::ERROR0005 . ": " . Config::ERROR_MESSAGE_0005);
            $this->logger->addError("User $username with uid $uid could not be verified");
            $result = new ResultDto(401, Config::ERROR0005, $exception->getMessage(), null);
            return $response->withJson($result, 401);
        }
    } else {
        $this->logger->addError(Config::ERROR0005 . ": " . Config::ERROR_MESSAGE_0005);
        $this->logger->addError("Received oath payload: $payload");
        $result = new ResultDto(401, Config::ERROR0005, "ID token could not be verified", null);
        return $response->withJson($result, 401);
    }
};

$app->get('/hello', function (Request $request, Response $response, array $args) {
    return $response->withJson(array("response" => "hello back"));
});

$app->get('/token', function (Request $request, Response $response, array $args) {

    $code = $request->getQueryParam("code");

    $client = new Google_Client();
    $client->setAuthConfig(Config::GOOGLE_CREDENTIALS);
    $client->setRedirectUri(Config::ROOT . "/api/token");
    $client->addScope(array(Google_Service_Oauth2::USERINFO_PROFILE, Google_Service_Oauth2::USERINFO_EMAIL));

    if (!empty($code)) {
        $client->fetchAccessTokenWithAuthCode($code);
        $token = $client->getAccessToken();
        return $response->withJson($token, 200, JSON_PRETTY_PRINT);
    } else {
        $gAuthUrl = $client->createAuthUrl();
        return $response->withRedirect($gAuthUrl, 303);
    }

});

$app->post('/tokensignin', function (Request $request, Response $response, array $args) {

    if ($this->db == null) {
        $this->logger->addError(Config::ERROR0001 . ": " . Config::ERROR_MESSAGE_0001);
        $result = new ResultDto(500, Config::ERROR0001, Config::ERROR_MESSAGE_0001, null);
        return $response->withJson($result, 500);
    }

    $id_token = $request->getQueryParam("id_token");

    if (empty($id_token)) {
        $this->logger->addError(Config::ERROR0007 . ": " . Config::ERROR_MESSAGE_0007);
        $result = new ResultDto(401, Config::ERROR0007, Config::ERROR_MESSAGE_0007, null);
        return $response->withJson($result, 401);
    }

    $client = new Google_Client(['client_id' => Config::CLIENT_ID]);  // Specify the CLIENT_ID of the app that accesses the backend
    $payload = $client->verifyIdToken($id_token);

    if ($payload) {
        $username = $payload['email'];
        $uid = $payload['sub'];
        $given_name = $payload['given_name'];
        $family_name = $payload['family_name'];
        $oauth_provider = Config::OAUTH_PROVIDER_GOOGLE;

        if (empty($username) || empty($uid)) {
            $this->logger->addError("0006: " . $this->error['0006']);
            $result = new ResultDto(400, '0006', $this->error['0006'], null);
            return $response->withJson($result, 400);
        }


        if ($this->recipeService->userExists($username)) {
            /** @var User $user */
            $user = $this->recipeService->getUser($username);
            $user->setUid($uid);
            $user->setGivenName($given_name);
            $user->setFamilyName($family_name);
            $user->setOauthProvider($oauth_provider);

            $this->recipeService->updateUser($user);
            $httpCode = 200;
        } else {
            $user = new User($username);
            $user->setUid($uid);
            $user->setGivenName($given_name);
            $user->setFamilyName($family_name);
            $user->setOauthProvider($oauth_provider);

            $this->recipeService->createUser($user);
            $httpCode = 201;
        }

        $data = array("id" => $user->getId(), "username" => $user->getUsername());
        $result = new ResultDto($httpCode, null, null, $data);
        return $response->withJson($result, $httpCode);
    } else {
        $this->logger->addError("0005: " . $this->error['0005']);
        $result = new ResultDto(401, '0005', $this->error['0005'], null);
        return $response->withJson($result, 401);
    }
});

$app->post('/sync', function (Request $request, Response $response, array $args) {

    $json = $request->getBody();
    $clientSyncData = json_decode($json, true);
    try {
        $parsedClientSyncData = new SyncDto($clientSyncData);
        $serverSyncData = $this->syncData($parsedClientSyncData);
        return $response->withJson(ResultDto::ok($serverSyncData));
    } catch (PDOException $e) {
        return $response->withJson(new ResultDto(500, Config::ERROR0009, $e->getMessage(), $clientSyncData), 500);
    }

})->add($errorHandling)->add($authenticate);

$app->get('/all', function (Request $request, Response $response, array $args) {

    $data = $this->recipeService->getAllForUser();
    return $response->withJson(ResultDto::ok($data));

})->add($errorHandling)->add($authenticate);

$app->put('/recipes', function (Request $request, Response $response, array $args) {

    $json = $request->getBody();
    $data = json_decode($json);
    try {
        if ($this->recipeService->updateRecipe(Recipe::fromJson($data))) {
            return $response->withJson(ResultDto::ok($data));
        } else {
            return $response->withJson(new ResultDto(500, "0000", $this->error['0000'], $data), 500);
        }
    } catch (PDOException $e) {
        return $response->withJson(new ResultDto(500, "0009", $e->getMessage(), $data), 500);
    }

})->add($errorHandling)->add($authenticate);

$app->post('/shopping-list-items', function (Request $request, Response $response, array $args) {

    $json = $request->getBody();
    $data = json_decode($json);
    $this->recipeService->insertShoppingListItem($data);
    return $response->withJson(ResultDto::ok($request->getBody()));

})->add($errorHandling)->add($authenticate);

$app->put('/recipe-ingredients', function (Request $request, Response $response, array $args) {

    $json = $request->getBody();
    $data = json_decode($json);
    try {
        if ($this->recipeService->updateRecipeIngredient(RecipeIngredient::fromJson($data))) {
            return $response->withJson(ResultDto::ok($data));
        } else {
            return $response->withJson(new ResultDto(500, "0000", $this->error['0000'], $data), 500);
        }
    } catch (PDOException $e) {
        return $response->withJson(new ResultDto(500, "0009", $e->getMessage(), $data), 500);
    }
})->add($errorHandling)->add($authenticate);

$app->put('/recipe-steps', function (Request $request, Response $response, array $args) {

    $json = $request->getBody();
    $data = json_decode($json);
    try {
        if ($this->recipeService->updateRecipeStep(RecipeStep::fromJson($data))) {
            return $response->withJson(ResultDto::ok($data));
        } else {
            return $response->withJson(new ResultDto(500, "0000", $this->error['0000'], $data), 500);
        }
    } catch (PDOException $e) {
        return $response->withJson(new ResultDto(500, "0009", $e->getMessage(), $data), 500);
    }
})->add($errorHandling)->add($authenticate);

$app->post('/users', function (Request $request, Response $response, array $args) {

    $username = $request->getQueryParam("username");
    $password = $request->getQueryParam("password");

    if (empty($username) || empty($password)) {
        $this->logger->addError("0002: " . $this->error['0002']);
        $result = new ResultDto(400, '0002', $this->error['0002'], null);
        return $response->withJson($result, 400);
    }

    if ($this->db == null) {
        $this->logger->addError("0001: " . $this->error['0001']);
        $result = new ResultDto(500, '0001', $this->error['0001'], null);
        return $response->withJson($result, 500);
    }

    $sql = "SELECT count(1) AS userCount FROM ab_user WHERE username = :username";
    /** @var PDOStatement $stmt */
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $data = $stmt->fetch();

    if ($data["userCount"] >= 1) {
        $this->logger->addError("0003: " . $this->error['0003']);
        $result = new ResultDto(400, '0003', $this->error['0003'], null);
        return $response->withJson($result, 400);
    }

    $user = new User($username);
    $user->hashPassword($password);
    $sql = "INSERT INTO ab_user (username, password_hash) VALUES (:username, :passwordHash)";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(":username", $user->getUsername());
    $stmt->bindValue(":passwordHash", $user->getUsername());
    $stmt->execute();

    $data = array("username" => $user->getUsername());

    $result = new ResultDto(201, null, null, $data);
    $newResponse = $response->withJson($result, 201);

    $connection = null;

    return $newResponse;
});

$app->run();
