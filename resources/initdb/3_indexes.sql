USE recipes_dev;

ALTER TABLE `ab_aisle`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQUE` (`name`) USING BTREE;

ALTER TABLE `ab_deleted`
    ADD PRIMARY KEY (`id`),
    ADD KEY `ab_deleted_ab_deleted_table_id_fk` (`table_id`),
    ADD KEY `ab_deleted_deleted_index` (`deleted`);

ALTER TABLE `ab_deleted_table`
    ADD PRIMARY KEY (`id`),
    ADD KEY `ab_deleted_table_name_index` (`name`),
    ADD KEY `ab_deleted_table_modified_index` (`modified`);

ALTER TABLE `ab_ingredient`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQUE` (`name`) USING BTREE,
    ADD KEY `fk_ab_ingredient_aisle_id_idx` (`aisle_id`),
    ADD KEY `ab_ingredient_modified_index` (`modified`);

ALTER TABLE `ab_oauth`
    ADD PRIMARY KEY (`id`),
    ADD KEY `fk_ab_oauth_user_id_idx` (`user_id`);

ALTER TABLE `ab_placement`
    ADD PRIMARY KEY (`id`),
    ADD KEY `fk_ab_placement_store_id_idx` (`store_id`),
    ADD KEY `fk_ab_placement_aisle_id_idx` (`aisle_id`);

ALTER TABLE `ab_recipe`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `ab_recipe_ingredient`
    ADD PRIMARY KEY (`id`),
    ADD KEY `fk_ab_recipe_ingredient_ingredient_id_idx` (`ingredient_id`),
    ADD KEY `fk_ab_recipe_ingredient_recipe_id_idx` (`recipe_id`),
    ADD KEY `fk_ab_recipe_ingredient_unit_id_idx` (`unit_id`),
    ADD KEY `ab_recipe_ingredient_modified_index` (`modified`);

ALTER TABLE `ab_recipe_step`
    ADD PRIMARY KEY (`id`),
    ADD KEY `fk_ab_recipe_step_recipe_id_idx` (`recipe_id`);

ALTER TABLE `ab_recipe_tag`
    ADD PRIMARY KEY (`id`),
    ADD KEY `fk_ab_recipe_tag_recipe_id` (`recipe_id`) USING BTREE,
    ADD KEY `fk_ab_recipe_tag_tag_id_idx` (`tag_id`);

ALTER TABLE `ab_recipe_user`
    ADD PRIMARY KEY (`id`),
    ADD KEY `INDEX_recipe_id` (`recipe_id`),
    ADD KEY `fk_ab_recipe_user_user_id_idx` (`user_id`);

ALTER TABLE `ab_store`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQUE` (`name`) USING BTREE;

ALTER TABLE `ab_sync`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `ab_tag`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQUE` (`name`) USING BTREE;

ALTER TABLE `ab_unit`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQUE name_singular` (`name_singular`) USING BTREE,
    ADD UNIQUE KEY `UNIQUE name_plural` (`name_plural`) USING BTREE;

ALTER TABLE `ab_user`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `username_UNIQUE` (`username`);

ALTER TABLE `ab_deleted`
    MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ab_deleted_table`
    MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 17;

ALTER TABLE `ab_sync`
    MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 2;

ALTER TABLE `ab_ingredient`
    ADD CONSTRAINT `fk_ab_ingredient_aisle_id` FOREIGN KEY (`aisle_id`) REFERENCES `ab_aisle` (`id`);

ALTER TABLE `ab_oauth`
    ADD CONSTRAINT `fk_ab_oauth_user_id` FOREIGN KEY (`user_id`) REFERENCES `ab_user` (`id`);

ALTER TABLE `ab_placement`
    ADD CONSTRAINT `fk_ab_placement_aisle_id` FOREIGN KEY (`aisle_id`) REFERENCES `ab_aisle` (`id`),
    ADD CONSTRAINT `fk_ab_placement_store_id` FOREIGN KEY (`store_id`) REFERENCES `ab_store` (`id`);

ALTER TABLE `ab_recipe_ingredient`
    ADD CONSTRAINT `fk_ab_recipe_ingredient_ingredient_id` FOREIGN KEY (`ingredient_id`) REFERENCES `ab_ingredient` (`id`),
    ADD CONSTRAINT `fk_ab_recipe_ingredient_recipe_id` FOREIGN KEY (`recipe_id`) REFERENCES `ab_recipe` (`id`),
    ADD CONSTRAINT `fk_ab_recipe_ingredient_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `ab_unit` (`id`);

ALTER TABLE `ab_recipe_step`
    ADD CONSTRAINT `fk_ab_recipe_step_recipe_id` FOREIGN KEY (`recipe_id`) REFERENCES `ab_recipe` (`id`);

ALTER TABLE `ab_recipe_tag`
    ADD CONSTRAINT `fk_ab_recipe_tag_recipe_id` FOREIGN KEY (`recipe_id`) REFERENCES `ab_recipe` (`id`),
    ADD CONSTRAINT `fk_ab_recipe_tag_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `ab_tag` (`id`);

ALTER TABLE `ab_recipe_user`
    ADD CONSTRAINT `fk_ab_recipe_user_recipe_id` FOREIGN KEY (`recipe_id`) REFERENCES `ab_recipe` (`id`),
    ADD CONSTRAINT `fk_ab_recipe_user_user_id` FOREIGN KEY (`user_id`) REFERENCES `ab_user` (`id`);
COMMIT;
