<?php
/**
 * Author: Yeray García Quintana
 * Date: 04.11.2018
 */

namespace Recipes\config;

class Config
{
    const DB_HOST = "recipes-db";
    const DB_USER = "recipes_user";
    const DB_PASS = "<enter-your-password-here>";
    const DB_NAME = "recipes_dev";
    const TESTDB_NAME = "recipes_test";

    const CLIENT_ID = "<enter-your-api-client-id-here>";
    const ROOT = "<enter-base-url-path-here>";
    const GOOGLE_REDIRECT_URL = "<enter-google-redirect-url-here>";
    const GOOGLE_CREDENTIALS = "<enter-google-credentials-json-file-here";

    const DISPLAY_ERROR_DETAILS = true;
    const ADD_CONTENT_LENGTH_HEADER = false;

    const OAUTH_PROVIDER_GOOGLE = 1;

    const ENTITY_PATH = "Recipes\\db\\entity\\";

    const TABLE_PREFIX = "ab_";
    const TABLE_SYNC = "ab_sync";
    const TABLE_DELETED = "ab_deleted";
    const TABLE_DELETED_TABLE = "ab_deleted_table";
    const TABLE_AISLE = "ab_aisle";
    const TABLE_UNIT = "ab_unit";
    const TABLE_TAG = "ab_tag";
    const TABLE_INGREDIENT = "ab_ingredient";
    const TABLE_RECIPE = "ab_recipe";
    const TABLE_RECIPE_INGREDIENT = "ab_recipe_ingredient";
    const TABLE_RECIPE_STEP = "ab_recipe_step";
    const TABLE_RECIPE_TAG = "ab_recipe_tag";
    const TABLE_RECIPE_USER = "ab_recipe_user";
    const TABLE_USER = "ab_user";

    const ERROR0000 = "0000";
    const ERROR0001 = "0001";
    const ERROR0002 = "0002";
    const ERROR0003 = "0003";
    const ERROR0004 = "0004";
    const ERROR0005 = "0005";
    const ERROR0006 = "0006";
    const ERROR0007 = "0007";
    const ERROR0008 = "0008";
    const ERROR0009 = "0009";
    const ERROR0010 = "0010";

    const ERROR_MESSAGE_0000 = "An error occurred";
    const ERROR_MESSAGE_0001 = "Couldn't connect to database";
    const ERROR_MESSAGE_0002 = "Username and password required";
    const ERROR_MESSAGE_0003 = "Username already exists";
    const ERROR_MESSAGE_0004 = "Wrong username or password";
    const ERROR_MESSAGE_0005 = "Authentication Error";
    const ERROR_MESSAGE_0006 = "Username and user id required";
    const ERROR_MESSAGE_0007 = "Parameter id_token cannot be empty";
    const ERROR_MESSAGE_0008 = "Token cannot be empty";
    const ERROR_MESSAGE_0009 = "A database error occurred";
    const ERROR_MESSAGE_0010 = "Error verifying id token";
}
