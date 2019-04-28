import units from "./units";

const nutrition = {
  getIngredientList(ingredients) {
    let ingredientList = "";
    ingredients.forEach(function(ingredient) {
      ingredientList +=
        ingredient.food_name.charAt(0).toUpperCase() +
        ingredient.food_name.slice(1) +
        ", ";
    });
    return ingredientList;
  },
  getTotals(ingredients, normalize = true) {
    let nutrition = {
      calories: 0,
      totalFat: 0,
      satFat: 0,
      transFat: 0,
      cholesterol: 0,
      sodium: 0,
      totalCarb: 0,
      fibers: 0,
      sugars: 0,
      proteins: 0,
      vitaminD: 0,
      potassium: 0,
      calcium: 0,
      iron: 0,
      addedSugars: 0
    };

    ingredients.forEach(ingredient => {
      let calciumIndex = _.findIndex(ingredient.full_nutrients, function(o) {
        return o.attr_id == 301;
      });
      let vitamindIndex = _.findIndex(ingredient.full_nutrients, function(o) {
        return o.attr_id == 324;
      });
      let ironIndex = _.findIndex(ingredient.full_nutrients, function(o) {
        return o.attr_id == 303;
      });
      let transfatIndex = _.findIndex(ingredient.full_nutrients, function(o) {
        return o.attr_id == 605;
      });
      let addedSugarIndex = _.findIndex(ingredient.full_nutrients, function(o) {
        return o.attr_id == 539;
      });

      let multiplier = 1;

      if (normalize) {
        multiplier = ingredient.quantity || 1;
      }

      if (
        normalize &&
        ingredient.quantity_unit != units.base(ingredient.unit_type)
      ) {
        multiplier *= units.convert(
          1,
          ingredient.quantity_unit,
          units.base(ingredient.unit_type),
          false
        );
      }

      nutrition.calories +=
        (ingredient.nf_calories || ingredient.calories) * multiplier;
      nutrition.totalFat +=
        (ingredient.nf_total_fat || ingredient.totalfat) * multiplier;
      nutrition.satFat +=
        (ingredient.nf_saturated_fat || ingredient.satfat) * multiplier;
      nutrition.transFat +=
        transfatIndex > -1
          ? ingredient.full_nutrients[transfatIndex].value
          : ingredient.transfat;
      nutrition.cholesterol +=
        (ingredient.nf_cholesterol || ingredient.cholesterol) * multiplier;
      nutrition.sodium +=
        (ingredient.nf_sodium || ingredient.sodium) * multiplier;
      nutrition.totalCarb +=
        (ingredient.nf_total_carbohydrate || ingredient.totalcarb) * multiplier;
      nutrition.fibers +=
        (ingredient.nf_dietary_fiber || ingredient.fibers) * multiplier;
      nutrition.sugars +=
        (ingredient.nf_sugars || ingredient.sugars) * multiplier;
      nutrition.addedSugars +=
        addedSugarIndex > -1
          ? ingredient.full_nutrients[addedSugarIndex].value
          : ingredient.addedSugars || 0;
      nutrition.proteins +=
        (ingredient.nf_protein || ingredient.proteins) * multiplier;
      nutrition.potassium +=
        (ingredient.nf_potassium || ingredient.potassium) * multiplier;
      nutrition.vitaminD +=
        vitamindIndex > -1
          ? ingredient.full_nutrients[vitamindIndex].value
          : ingredient.vitamind || 0;
      nutrition.calcium +=
        calciumIndex > -1
          ? ingredient.full_nutrients[calciumIndex].value
          : ingredient.calcium || 0;
      nutrition.iron +=
        ironIndex > -1
          ? ingredient.full_nutrients[ironIndex].value
          : ingredient.iron || 0;
    });

    return nutrition;
  }
};

export default nutrition;
