<?php
/**
 * Author: Yeray García Quintana
 * Date: 05.08.2018
 */

use Recipes\config\Config;
use Recipes\db\Database;
use Recipes\model\RecipeService;

require_once '../../vendor/autoload.php';

$recipeService = new RecipeService(Database::getInstance());

session_start();

$client = new Google_Client();
$client->setAuthConfig(Config::GOOGLE_CREDENTIALS);
$client->setRedirectUri(Config::GOOGLE_REDIRECT_URL);
$client->addScope(array(Google_Service_Oauth2::USERINFO_PROFILE, Google_Service_Oauth2::USERINFO_EMAIL));

function logout($client)
{
    session_destroy();
    $client->revokeToken();
    header('Location: ' . filter_var(Config::GOOGLE_REDIRECT_URL, FILTER_SANITIZE_URL));
    exit;
}

if (isset($_GET['logout'])) {
    logout($client);
}

if (isset($_GET['code'])) {
    $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['token'] = $client->getAccessToken();
    header('Location: ' . filter_var(Config::GOOGLE_REDIRECT_URL, FILTER_SANITIZE_URL));
    exit;
}

if (isset($_SESSION['token']) && $_SESSION['token']) {

    $client->setAccessToken($_SESSION['token']);
}

if ($client->getAccessToken()) {

    $_SESSION['token'] = $client->getAccessToken();

    try {

        // for logged in user, get details from google using access token
        $oauth = new Google_Service_Oauth2($client);
        $gUserInfo = $oauth->userinfo->get();

        $gUser['google_id'] = $gUserInfo['id'];
        $gUser['google_fullname'] = filter_var($gUserInfo['name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $gUser['google_givenname'] = filter_var($gUserInfo['given_name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $gUser['google_lastname'] = filter_var($gUserInfo['family_name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $gUser['google_email'] = filter_var($gUserInfo['email'], FILTER_SANITIZE_EMAIL);
        $gUser['google_link'] = filter_var($gUserInfo['link'], FILTER_VALIDATE_URL);
        $gUser['google_picture_link'] = filter_var($gUserInfo['picture'], FILTER_VALIDATE_URL);

    } catch (Exception $e) {
        logout($client);
    }

    $username = $gUserInfo->getEmail();
    $uid = $gUserInfo->getId();

    if (empty($username) || empty($uid)) {
        logout($client);
    }

    if ($userId = $recipeService->verifyOAuthUser($username, $uid)) {
        $_SESSION['userId'] = $userId;
        $_SESSION['loggedIn'] = 1;
    } else {
        logout($client);
    }

} else {
    // for Guest user, get google login url
    $gAuthUrl = $client->createAuthUrl();
    $_SESSION['loggedIn'] = 0;
}
