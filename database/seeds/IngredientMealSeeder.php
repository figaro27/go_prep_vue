<?php

use Illuminate\Database\Seeder;

class IngredientMealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        
    	$createIngredientsMealsTable = "
    		CREATE TABLE IF NOT EXISTS `ingredient_meal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ingredient_id` int(10) unsigned NOT NULL,
  `meal_id` int(10) unsigned NOT NULL,
  `quantity` double(8,2) NOT NULL,
  `quantity_unit` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_unit_display` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ingredient_meal_ingredient_id_meal_id_quantity_unit_unique` (`ingredient_id`,`meal_id`,`quantity_unit`),
  KEY `ingredient_meal_meal_id_foreign` (`meal_id`),
  CONSTRAINT `ingredient_meal_ingredient_id_foreign` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`),
  CONSTRAINT `ingredient_meal_meal_id_foreign` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    	";

    	$insertIngredientsMeals = "
    		INSERT INTO `ingredient_meal` VALUES (1,1,1,5.00,'oz','oz',NULL,NULL),(2,2,1,3.00,'oz','oz',NULL,NULL),(3,3,1,1.00,'tsp','tsp',NULL,NULL),(4,4,1,5.00,'oz','oz',NULL,NULL),(5,5,2,4.00,'oz','oz',NULL,NULL),(6,6,2,2.00,'oz','oz',NULL,NULL),(7,7,2,2.00,'oz','oz',NULL,NULL),(8,8,2,2.00,'Tbs','Tbs',NULL,NULL),(9,4,2,4.00,'oz','oz',NULL,NULL),(10,9,18,2.00,'unit','slices',NULL,NULL),(11,10,18,6.00,'fl-oz','Tbs',NULL,NULL),(12,11,18,1.00,'Tbs','Tbs',NULL,NULL),(13,12,18,0.50,'oz','oz',NULL,NULL),(14,13,18,2.00,'unit','large',NULL,NULL),(15,14,3,1.00,'oz','oz',NULL,NULL),(16,15,3,1.00,'oz','oz',NULL,NULL),(17,7,3,1.00,'oz','oz',NULL,NULL),(18,8,3,1.00,'Tbs','Tbs',NULL,NULL),(19,5,3,5.00,'oz','oz',NULL,NULL),(20,4,3,5.00,'oz','oz',NULL,NULL),(21,16,4,8.00,'oz','oz',NULL,NULL),(22,17,4,5.00,'oz','oz',NULL,NULL),(23,18,4,5.00,'oz','oz',NULL,NULL),(24,3,4,1.00,'tsp','tsp',NULL,NULL),(25,19,20,10.00,'oz','oz',NULL,NULL),(26,20,20,0.67,'cup','cup',NULL,NULL),(27,21,5,6.00,'oz','oz',NULL,NULL),(28,6,5,2.00,'oz','oz',NULL,NULL),(29,7,5,2.00,'oz','oz',NULL,NULL),(30,4,5,5.00,'oz','oz',NULL,NULL),(31,22,5,2.00,'Tbs','Tbs',NULL,NULL),(32,23,6,5.00,'oz','oz',NULL,NULL),(33,24,6,3.00,'Tbs','cup',NULL,NULL),(34,4,6,5.00,'oz','oz',NULL,NULL),(35,25,7,7.00,'oz','oz',NULL,NULL),(36,4,7,5.00,'oz','oz',NULL,NULL),(37,26,7,1.00,'Tbs','Tbs',NULL,NULL),(38,27,7,2.00,'Tbs','Tbs',NULL,NULL),(39,28,8,4.00,'oz','oz',NULL,NULL),(40,23,8,7.00,'oz','oz',NULL,NULL),(41,4,8,5.00,'oz','oz',NULL,NULL),(42,22,8,2.00,'Tbs','Tbs',NULL,NULL),(43,29,21,0.10,'cup','cup',NULL,NULL),(44,30,21,0.10,'cup','cup',NULL,NULL),(45,31,21,0.10,'tsp','tsp',NULL,NULL),(46,32,21,0.20,'Tbs','Tbs',NULL,NULL),(47,33,21,0.10,'cup','cup',NULL,NULL),(48,34,21,0.25,'oz','oz',NULL,NULL),(49,13,21,0.25,'unit','large',NULL,NULL),(50,35,21,0.10,'tsp','tsp',NULL,NULL),(51,36,9,4.00,'oz','oz',NULL,NULL),(52,16,9,7.00,'oz','oz',NULL,NULL),(53,37,9,5.00,'Tbs','Tbs',NULL,NULL),(54,38,9,2.00,'oz','oz',NULL,NULL),(55,39,23,1.00,'oz','oz',NULL,NULL),(56,40,22,0.50,'Tbs','cup',NULL,NULL),(57,41,22,1.00,'tsp','Tbs',NULL,NULL),(58,42,22,1.00,'Tbs','Tbs',NULL,NULL),(59,43,22,1.00,'oz','oz',NULL,NULL),(60,44,22,1.00,'oz','oz',NULL,NULL),(61,45,22,1.00,'Tbs','Tbs',NULL,NULL),(62,46,10,0.50,'cup','cup',NULL,NULL),(63,47,10,8.00,'oz','oz',NULL,NULL),(64,48,10,1.00,'oz','oz',NULL,NULL),(65,4,10,4.00,'oz','oz',NULL,NULL),(66,49,16,0.75,'cup','cup',NULL,NULL),(67,50,16,1.00,'unit','medium (7\" to 7-7/8\" long)',NULL,NULL),(68,13,16,1.00,'unit','large',NULL,NULL),(69,51,16,0.50,'cup','cup',NULL,NULL),(70,31,16,4.00,'tsp','tsp',NULL,NULL),(71,52,16,0.12,'tsp','tsp',NULL,NULL),(72,53,16,0.12,'tsp','tsp',NULL,NULL),(73,54,16,0.25,'cup','cup',NULL,NULL),(74,55,16,1.00,'tsp','tsp',NULL,NULL),(75,56,17,1.00,'cup','cup',NULL,NULL),(76,57,17,1.00,'unit','large',NULL,NULL),(77,58,17,3.00,'Tbs','Tbs',NULL,NULL),(78,31,17,1.00,'tsp','tsp',NULL,NULL),(79,59,17,0.25,'tsp','tsp',NULL,NULL),(80,55,17,4.00,'tsp','tsp',NULL,NULL),(81,60,11,8.00,'oz','oz',NULL,NULL),(82,18,11,4.00,'oz','oz',NULL,NULL),(83,61,11,4.00,'oz','oz',NULL,NULL),(84,62,12,8.00,'oz','oz',NULL,NULL),(85,63,12,2.00,'Tbs','Tbs',NULL,NULL),(86,64,12,2.00,'oz','oz',NULL,NULL),(87,38,12,2.00,'oz','oz',NULL,NULL),(88,4,12,5.00,'oz','oz',NULL,NULL),(89,65,13,2.00,'cup','cup',NULL,NULL),(90,17,13,3.00,'oz','oz',NULL,NULL),(91,66,14,2.00,'unit','pepper',NULL,NULL),(92,67,14,5.00,'oz','oz',NULL,NULL),(93,68,14,2.00,'tsp','tsp',NULL,NULL),(94,4,14,2.00,'oz','oz',NULL,NULL),(95,15,14,3.00,'oz','oz',NULL,NULL),(96,65,15,1.50,'cup','cup',NULL,NULL),(97,36,15,5.00,'oz','oz',NULL,NULL),(98,69,19,1.00,'cup','cup',NULL,NULL),(99,70,19,0.50,'cup','cup',NULL,NULL),(100,71,19,3.00,'oz','oz',NULL,NULL);

    	";

    DB::statement($createIngredientsMealsTable);
    DB::statement($insertIngredientsMeals);

    }
}
