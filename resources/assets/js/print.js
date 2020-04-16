import nutritionFacts from "nutrition-label-jquery-plugin";
import _ from "lodash";
window._ = _;

import units from "./data/units";
import Nutrition from "./data/nutrition";
import format from "./lib/format";

try {
  $(document).ready(() => {
    $(".nutritionFacts").each(function() {
      var el = $(this);
      const meal = el.data("meal");

      const ingredients = meal.ingredients;

      const nutrition = Nutrition.getTotals(ingredients);
      const ingredientList = Nutrition.getIngredientList(ingredients);
      const servingsPerMeal = meal.servingsPerMeal ? meal.servingsPerMeal : 1;
      const servingSizeUnit = meal.servingSizeUnit
        ? meal.servingSizeUnit
        : "1 Meal";
      const showIngredients = false;

      el.nutritionLabel({
        showItemName: false,

        showPolyFat: false,
        showMonoFat: false,
        showTransFat: false,
        showFibers: false,
        showVitaminD: false,
        showPotassium_2018: false,
        showCalcium: false,
        showIron: false,
        showCaffeine: false,
        itemName: meal.title ? meal.title : "",
        ingredientList: ingredientList,
        showIngredients: showIngredients,
        decimalPlacesForQuantityTextbox: 2,
        allowFDARounding: false,
        decimalPlacesForNutrition: 0,
        valueCalories: nutrition.calories / servingsPerMeal,
        valueFatCalories: nutrition.fatCalories / servingsPerMeal,
        valueTotalFat: nutrition.totalfat / servingsPerMeal,
        valueSatFat: nutrition.satfat / servingsPerMeal,
        valueTransFat: nutrition.transfat / servingsPerMeal,
        valueCholesterol: nutrition.cholesterol / servingsPerMeal,
        valueSodium: nutrition.sodium / servingsPerMeal,
        valueTotalCarb: nutrition.totalcarb / servingsPerMeal,
        valueFibers: nutrition.fibers / servingsPerMeal,
        valueSugars: nutrition.sugars / servingsPerMeal,
        valueProteins: nutrition.proteins / servingsPerMeal,
        // valueVitaminD: ((nutrition.vitamind / 20000) * 100) / servingsPerMeal,
        // valuePotassium_2018:
        //   ((nutrition.potassium / 4700) * 100) / servingsPerMeal,
        // valueCalcium: ((nutrition.calcium / 1300) * 100) / servingsPerMeal,
        // valueIron: ((nutrition.iron / 18) * 100) / servingsPerMeal,
        valueAddedSugars: nutrition.addedSugars / servingsPerMeal,
        showLegacyVersion: false
      });
    });

    window.status = "ready";
  });
} catch (e) {
  document.writeln(e);
}
