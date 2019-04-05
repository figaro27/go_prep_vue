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
        DB::statement($createIngredientsMealsTable);

        $m = 0;
        for ($i = 0; $i <= 639; $i += 71) {
            $insertIngredientsMeals = "
    		INSERT INTO `ingredient_meal` 
        (ingredient_id,meal_id,quantity,quantity_unit,quantity_unit_display,created_at,updated_at)
        VALUES 
        (1+$i,1+$m,5.00,'oz','oz',NULL,NULL),
        (2+$i,1+$m,3.00,'oz','oz',NULL,NULL),
        (3+$i,1+$m,1.00,'tsp','tsp',NULL,NULL),
        (4+$i,1+$m,5.00,'oz','oz',NULL,NULL),
        (5+$i,2+$m,4.00,'oz','oz',NULL,NULL),
        (6+$i,2+$m,2.00,'oz','oz',NULL,NULL),
        (7+$i,2+$m,2.00,'oz','oz',NULL,NULL),
        (8+$i,2+$m,2.00,'Tbs','Tbs',NULL,NULL),
        (4+$i,2+$m,4.00,'oz','oz',NULL,NULL),
        (9+$i,18+$m,2.00,'unit','slices',NULL,NULL),
        (10+$i,18+$m,6.00,'fl-oz','Tbs',NULL,NULL),
        (11+$i,18+$m,1.00,'Tbs','Tbs',NULL,NULL),
        (12+$i,18+$m,0.50,'oz','oz',NULL,NULL),
        (13+$i,18+$m,2.00,'unit','large',NULL,NULL),
        (14+$i,3+$m,1.00,'oz','oz',NULL,NULL),
        (15+$i,3+$m,1.00,'oz','oz',NULL,NULL),
        (7+$i,3+$m,1.00,'oz','oz',NULL,NULL),
        (8+$i,3+$m,1.00,'Tbs','Tbs',NULL,NULL),
        (5+$i,3+$m,5.00,'oz','oz',NULL,NULL),
        (4+$i,3+$m,5.00,'oz','oz',NULL,NULL),
        (16+$i,4+$m,8.00,'oz','oz',NULL,NULL),
        (17+$i,4+$m,5.00,'oz','oz',NULL,NULL),
        (18+$i,4+$m,5.00,'oz','oz',NULL,NULL),
        (3+$i,4+$m,1.00,'tsp','tsp',NULL,NULL),
        (19+$i,20+$m,10.00,'oz','oz',NULL,NULL),
        (20+$i,20+$m,0.67,'cup','cup',NULL,NULL),
        (21+$i,5+$m,6.00,'oz','oz',NULL,NULL),
        (6+$i,5+$m,2.00,'oz','oz',NULL,NULL),
        (7+$i,5+$m,2.00,'oz','oz',NULL,NULL),
        (4+$i,5+$m,5.00,'oz','oz',NULL,NULL),
        (22+$i,5+$m,2.00,'Tbs','Tbs',NULL,NULL),
        (23+$i,6+$m,5.00,'oz','oz',NULL,NULL),
        (24+$i,6+$m,3.00,'Tbs','cup',NULL,NULL),
        (4+$i,6+$m,5.00,'oz','oz',NULL,NULL),
        (25+$i,7+$m,7.00,'oz','oz',NULL,NULL),
        (4+$i,7+$m,5.00,'oz','oz',NULL,NULL),
        (26+$i,7+$m,1.00,'Tbs','Tbs',NULL,NULL),
        (27+$i,7+$m,2.00,'Tbs','Tbs',NULL,NULL),
        (28+$i,8+$m,4.00,'oz','oz',NULL,NULL),
        (23+$i,8+$m,7.00,'oz','oz',NULL,NULL),
        (4+$i,8+$m,5.00,'oz','oz',NULL,NULL),
        (22+$i,8+$m,2.00,'Tbs','Tbs',NULL,NULL),
        (29+$i,21+$m,0.10,'cup','cup',NULL,NULL),
        (30+$i,21+$m,0.10,'cup','cup',NULL,NULL),
        (31+$i,21+$m,0.10,'tsp','tsp',NULL,NULL),
        (32+$i,21+$m,0.20,'Tbs','Tbs',NULL,NULL),
        (33+$i,21+$m,0.10,'cup','cup',NULL,NULL),
        (34+$i,21+$m,0.25,'oz','oz',NULL,NULL),
        (13+$i,21+$m,0.25,'unit','large',NULL,NULL),
        (35+$i,21+$m,0.10,'tsp','tsp',NULL,NULL),
        (36+$i,9+$m,4.00,'oz','oz',NULL,NULL),
        (16+$i,9+$m,7.00,'oz','oz',NULL,NULL),
        (37+$i,9+$m,5.00,'Tbs','Tbs',NULL,NULL),
        (38+$i,9+$m,2.00,'oz','oz',NULL,NULL),
        (39+$i,23+$m,1.00,'oz','oz',NULL,NULL),
        (40+$i,22+$m,0.50,'Tbs','cup',NULL,NULL),
        (41+$i,22+$m,1.00,'tsp','Tbs',NULL,NULL),
        (42+$i,22+$m,1.00,'Tbs','Tbs',NULL,NULL),
        (43+$i,22+$m,1.00,'oz','oz',NULL,NULL),
        (44+$i,22+$m,1.00,'oz','oz',NULL,NULL),
        (45+$i,22+$m,1.00,'Tbs','Tbs',NULL,NULL),
        (46+$i,10+$m,0.50,'cup','cup',NULL,NULL),
        (47+$i,10+$m,8.00,'oz','oz',NULL,NULL),
        (48+$i,10+$m,1.00,'oz','oz',NULL,NULL),
        (4+$i,10+$m,4.00,'oz','oz',NULL,NULL),
        (49+$i,16+$m,0.75,'cup','cup',NULL,NULL),
        (50+$i,16+$m,1.00,'unit','medium (7\" to 7-7/8\" long)',NULL,NULL),
        (13+$i,16+$m,1.00,'unit','large',NULL,NULL),
        (51+$i,16+$m,0.50,'cup','cup',NULL,NULL),
        (31+$i,16+$m,4.00,'tsp','tsp',NULL,NULL),
        (52+$i,16+$m,0.12,'tsp','tsp',NULL,NULL),
        (53+$i,16+$m,0.12,'tsp','tsp',NULL,NULL),
        (54+$i,16+$m,0.25,'cup','cup',NULL,NULL),
        (55+$i,16+$m,1.00,'tsp','tsp',NULL,NULL),
        (56+$i,17+$m,1.00,'cup','cup',NULL,NULL),
        (57+$i,17+$m,1.00,'unit','large',NULL,NULL),
        (58+$i,17+$m,3.00,'Tbs','Tbs',NULL,NULL),
        (31+$i,17+$m,1.00,'tsp','tsp',NULL,NULL),
        (59+$i,17+$m,0.25,'tsp','tsp',NULL,NULL),
        (55+$i,17+$m,4.00,'tsp','tsp',NULL,NULL),
        (60+$i,11+$m,8.00,'oz','oz',NULL,NULL),
        (18+$i,11+$m,4.00,'oz','oz',NULL,NULL),
        (61+$i,11+$m,4.00,'oz','oz',NULL,NULL),
        (62+$i,12+$m,8.00,'oz','oz',NULL,NULL),
        (63+$i,12+$m,2.00,'Tbs','Tbs',NULL,NULL),
        (64+$i,12+$m,2.00,'oz','oz',NULL,NULL),
        (38+$i,12+$m,2.00,'oz','oz',NULL,NULL),
        (4+$i,12+$m,5.00,'oz','oz',NULL,NULL),
        (65+$i,13+$m,2.00,'cup','cup',NULL,NULL),
        (17+$i,13+$m,3.00,'oz','oz',NULL,NULL),
        (66+$i,14+$m,2.00,'unit','pepper',NULL,NULL),
        (67+$i,14+$m,5.00,'oz','oz',NULL,NULL),
        (68+$i,14+$m,2.00,'tsp','tsp',NULL,NULL),
        (4+$i,14+$m,2.00,'oz','oz',NULL,NULL),
        (15+$i,14+$m,3.00,'oz','oz',NULL,NULL),
        (65+$i,15+$m,1.50,'cup','cup',NULL,NULL),
        (36+$i,15+$m,5.00,'oz','oz',NULL,NULL),
        (69+$i,19+$m,1.00,'cup','cup',NULL,NULL),
        (70+$i,19+$m,0.50,'cup','cup',NULL,NULL),
        (71+$i,19+$m,3.00,'oz','oz',NULL,NULL);

    	";
            DB::statement($insertIngredientsMeals);
            $m += 23;
        }
    }
}
