CREATE DATABASE recipes_dev COLLATE utf8_unicode_ci;
CREATE DATABASE recipes_test COLLATE utf8_unicode_ci;
GRANT ALL PRIVILEGES ON `recipes\_dev`.* TO 'recipes_user'@'%';
GRANT ALL PRIVILEGES ON `recipes\_test`.* TO 'recipes_user'@'%';

USE recipes_dev;

CREATE TABLE `ab_aisle` (
    `id`   BINARY(16)                          NOT NULL,
    `name` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_deleted` (
    `id`         INT(11)    NOT NULL,
    `table_id`   INT(11)    NOT NULL,
    `deleted_id` BINARY(16) NOT NULL,
    `deleted`    TIMESTAMP  NOT NULL DEFAULT current_timestamp()
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_deleted_table` (
    `id`       INT(11)                              NOT NULL,
    `name`     VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `created`  TIMESTAMP                            NOT NULL DEFAULT current_timestamp(),
    `modified` TIMESTAMP                            NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_ingredient` (
    `id`       BINARY(16)                           NOT NULL,
    `name`     VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
    `aisle_id` BINARY(16)                           NOT NULL DEFAULT 0x11e8ab707b8ee0d7a87ee683eb7daae4,
    `created`  TIMESTAMP                            NOT NULL DEFAULT current_timestamp(),
    `modified` TIMESTAMP                            NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_oauth` (
    `id`             BINARY(16)                          NOT NULL,
    `oauth_provider` TINYINT(4)                          NOT NULL,
    `uid`            VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
    `user_id`        BINARY(16)                          NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_placement` (
    `id`         BINARY(16) NOT NULL,
    `aisle_id`   BINARY(16) NOT NULL,
    `store_id`   BINARY(16) NOT NULL,
    `sort_order` INT(11)    NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_recipe_ingredient` (
    `id`            BINARY(16) NOT NULL,
    `recipe_id`     BINARY(16) NOT NULL,
    `ingredient_id` BINARY(16) NOT NULL,
    `quantity`      DECIMAL(10, 2)      DEFAULT NULL,
    `unit_id`       BINARY(16)          DEFAULT NULL,
    `sort_order`    INT(11)    NOT NULL,
    `created`       TIMESTAMP  NOT NULL DEFAULT current_timestamp(),
    `modified`      TIMESTAMP  NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_recipe` (
    `id`       BINARY(16)                           NOT NULL,
    `name`     VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
    `portions` INT(11)                              NOT NULL,
    `duration` INT(11)                              NOT NULL,
    `url`      VARCHAR(255) COLLATE utf8_unicode_ci          DEFAULT NULL,
    `created`  TIMESTAMP                            NOT NULL DEFAULT current_timestamp(),
    `modified` TIMESTAMP                            NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_recipe_step` (
    `id`          BINARY(16)                   NOT NULL,
    `recipe_id`   BINARY(16)                   NOT NULL,
    `description` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `is_section`  TINYINT(1)                   NOT NULL,
    `sort_order`  INT(11)                      NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_recipe_tag` (
    `id`        BINARY(16) NOT NULL,
    `recipe_id` BINARY(16) NOT NULL,
    `tag_id`    BINARY(16) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_recipe_user` (
    `id`        BINARY(16) NOT NULL,
    `recipe_id` BINARY(16) NOT NULL,
    `user_id`   BINARY(16) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_store` (
    `id`   BINARY(16)                          NOT NULL,
    `name` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_sync` (
    `id`          INT(11)  NOT NULL,
    `last_update` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_tag` (
    `id`          BINARY(16)                          NOT NULL,
    `name`        VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
    `usage_count` BIGINT(20)                          NOT NULL DEFAULT 0,
    `last_used`   DATETIME                            NOT NULL DEFAULT current_timestamp()
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_unit` (
    `id`            BINARY(16)                          NOT NULL,
    `name_singular` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
    `name_plural`   VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `ab_user` (
    `id`            BINARY(16)                          NOT NULL,
    `username`      VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
    `password_hash` VARCHAR(64) COLLATE utf8_unicode_ci  DEFAULT NULL,
    `given_name`    VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `family_name`   VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;
