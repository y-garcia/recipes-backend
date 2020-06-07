UPDATE ab_sync
SET last_update = (
  SELECT max(modified)
  FROM (
         SELECT max(modified) AS modified
         FROM ab_ingredient
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_recipe_user
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_recipe_step
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_recipe_tag
         UNION
         SELECT max(modified) AS modified
         FROM ab_recipe_ingredient
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_recipe
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_placement
         UNION
         SELECT max(modified) AS modified
         FROM ab_ingredient
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_oauth
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_deleted
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_store
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_tag
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_unit
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_aisle
         UNION
         SELECT '1900-01-01 00:00:00' AS modified
         FROM ab_user
         UNION
         SELECT max(modified) AS modified
         FROM ab_deleted_table
       ) modified_per_table)
WHERE id = 1
