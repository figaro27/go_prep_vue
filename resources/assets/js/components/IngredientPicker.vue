<template>
  <div>
    <img src="/images/nutritionix.png" class="nutritionix mb-3">
    <b-form class="mb-2" @submit.prevent="searchRecipe">
      <b-tabs class="mb-2">
        <b-tab title="Type in Ingredients" active>
          <b-form-textarea
            v-model="recipe"
            class="flex-grow-1 mr-1 mb-1"
            :rows="3"
            placeholder="Enter a query like &quot;1 cup mashed potatoes and 2 tbsp gravy&quot;. Be sure to include accurate measurement names such as tsp, tbsp, cup, gram, oz, fl oz, etc.."
          ></b-form-textarea>
          <b-button @click="searchRecipe" variant="primary">Add</b-button>
        </b-tab>

        <b-tab title="Search Ingredients">
          <ingredient-search @change="onSearchIngredient"></ingredient-search>
        </b-tab>

        <b-tab title="Add Existing Ingredients">
          <div class="d-flex">
            <v-select
              class="flex-grow-1 mr-1"
              placeholder="Or search from your saved ingredients"
              :options="existingIngredientOptions"
              v-model="selectedExistingIngredients"
              multiple
            ></v-select>
            <b-button @click="onClickAddExistingIngredient" variant="primary">Add</b-button>
          </div>
        </b-tab>
      </b-tabs>
    </b-form>

    <div class="d-flex">
      <table class="table w-100 ingredients-table">
        <thead>
          <th>Name</th>
          <th>Weight</th>
          <th>Units</th>
          <th style="width: 30px"></th>
        </thead>
        <tbody>
          <tr v-for="(ingredient, i) in ingredients" :key="ingredient.food_name">
            <td>
              <img
                :src="ingredient.image_thumb"
                v-if="ingredient.image_thumb"
                class="ingredient-thumb"
              >
              {{ ingredient.food_name }}
            </td>
            <td>
              <b-form-group>
                <b-form-input placeholder="Weight" v-model="ingredient.quantity"></b-form-input>
              </b-form-group>
            </td>
            <td class="text-center">
              <b-form-group>
                <b-select
                  v-if="ingredient.unit_type !== 'unit'"
                  v-model="ingredient.quantity_unit"
                  :options="unitOptions(ingredient)"
                  style="width: 60px"
                >
                  <option slot="top" disabled>-- Select unit --</option>
                </b-select>
                <span v-else>{{ingredient.quantity_unit_display}}</span>
              </b-form-group>
            </td>
            <td>
              <b-btn variant="link" @click="removeIngredient(i)">
                <i class="fa fa-close"></i>
              </b-btn>
            </td>
          </tr>
          <tr>
            <td colspan="10" class="text-right">
              <b-row>
                <b-col v-if="options.saveButton" class="text-left">
                  <b-button variant="primary" :disabled="!canSave" @click.prevent="save">Save</b-button>
                </b-col>
                <!-- <b-col class="text-right">
                  <a href="#" @click.prevent="onClickAddIngredient">
                    <i class="fas fa-plus-circle"></i>
                  </a>
                </b-col>-->
              </b-row>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="ml-5" ref="nutritionFacts"></div>
    </div>
  </div>
</template>
<style lang="scss">
.ingredient-dropdown {
  display: block;
  width: 100%;
}
.ingredients-table {
  th,
  td {
    &:last-child {
      padding-left: 0;
      padding-right: 0;
      width: 30px;
    }
  }

  .ingredient-thumb {
    display: inline-block;
    width: 38px;
    height: auto;
    vertical-align: middle;
    margin-right: 5px;
  }
}
.ingredient-table {
  th,
  td {
    &:last-child {
      padding-left: 0;
      padding-right: 0;
      width: 35px;
    }
  }
}
</style>

<script>
import { mapGetters, mapActions } from "vuex";
import units from "../data/units";
import format from "../lib/format";

export default {
  props: {
    value: {},
    options: {
      default: {
        saveButton: false
      }
    },
    meal: {
      default: {
        title: ""
      }
    }
  },
  data() {
    return {
      recipe: "",
      ingredients: [],
      newIngredients: [],
      ingredientOptions: [],

      selectedExistingIngredients: []
    };
  },
  computed: {
    ...mapGetters({
      existingIngredients: "ingredients",
      defaultWeightUnit: "defaultWeightUnit"
    }),
    existingIngredientOptions() {
      return Object.values(this.existingIngredients).map(ingredient => {
        return {
          value: ingredient,
          text: ingredient.food_name,
          label: ingredient.food_name
        };
      });
    },
    unitOptions: () => ingredient => {
      let type = ingredient.unit_type;
      return units[type].selectOptions();
    },
    canSave() {
      return true;
      //return this.ingredients.length > 0;
    }
  },
  watch: {
    ingredients: {
      handler: function(newIngredients, oldIngredients) {
        if (!_.isEqual(newIngredients, oldIngredients)) {
          this.update();
        }
        this.getNutritionFacts(newIngredients);
      },
      deep: true
    }
  },
  created() {
    this.ingredients = _.isArray(this.value) ? this.value : [];
  },
  mounted() {
    this.refreshIngredients();
  },
  methods: {
    ...mapActions(["refreshIngredients"]),
    onClickAddIngredient() {
      this.ingredients.push({
        food_name: "",
        quantity: 1,
        unit_type: "mass",
        quantity_unit: "oz"
      });
    },
    onClickAddExistingIngredient() {
      this.selectedExistingIngredients.forEach(ingredient => {
        let val = { ...ingredient.value };
        val.quantity = 1;
        val.quantity_unit = units.base(val.unit_type);

        this.ingredients.push(val);
      });

      this.selectedExistingIngredients = [];
    },
    // Adding ingredient from table.
    onAddIngredient(val, i) {
      if (val) {
        // Already added or empty name
        if (val.id || val.added || _.isEmpty(val.food_name)) {
          return;
        }
        if (!val.unit_type) {
          val.unit_type = units.type(units.normalize(val.serving_unit));
        }
        if (!val.quantity) {
          val.quantity = 1;
        }
        if (!val.quantity_unit) {
          val.quantity_unit = units.normalize(val.serving_unit);
        }

        // Calculate nutrition for 1 baseunit
        let multiplier = units.convert(
          1,
          units.base(val.unit_type),
          val.quantity_unit,
          false
        );
        let nutrition = this.getNutritionTotals([val], false);
        if (nutrition) {
          nutrition = _.mapValues(nutrition, prop => {
            return prop * multiplier;
          });
          val = _.merge(val, nutrition);
        }

        this.ingredients[i] = val;
      } else {
        this.removeIngredient(i);
      }
    },
    removeIngredient(i) {
      Vue.delete(this.ingredients, i);
    },
    update() {
      this.$emit("input", this.ingredients);
    },
    save() {
      this.$emit("save", this.ingredients);
      this.$toastr.s("Ingredients saved!");
    },
    searchInstant: function() {},
    searchRecipe: function() {
      axios
        .post("/api/nutrients", {
          query: this.recipe
        })
        .then(response => {
          let newIngredients = _.map(response.data.foods, ingredient => {
            // Get properly named unit
            let unit = units.normalize(ingredient.serving_unit);
            ingredient.unit_type = units.type(unit);
            ingredient.quantity_unit_display = unit;

            if (ingredient.unit_type === "unit") {
              unit = "unit";
            }
            ingredient.quantity = ingredient.serving_qty;
            ingredient.quantity_unit = unit;
            ingredient.added = true;
            ingredient.image_thumb = ingredient.photo.thumb || null;

            // Calculate nutrition for 1 baseunit
            let multiplier =
              units.convert(1, units.base(ingredient.unit_type), unit, false) /
              ingredient.quantity;
            let nutrition = this.getNutritionTotals([ingredient], false);
            if (nutrition) {
              nutrition = _.mapValues(nutrition, prop => {
                return prop * multiplier;
              });
              ingredient = _.merge(ingredient, nutrition);
            }

            return ingredient;
          });
          this.ingredients = _.concat(this.ingredients, newIngredients);
          this.recipe = "";
        })
        .catch(e => {
          this.$toastr.e("No ingredients found.", "Sorry!");
        });
    },
    onSearch(search, loading) {
      loading(true);
      this.search(loading, search, this);
    },
    search: _.debounce((loading, search, vm) => {
      axios
        .post("/api/searchInstant", {
          search: search
        })
        .then(response => {
          vm.ingredientOptions = response.data.common;
          loading(false);
        });
    }, 350),
    onSearchIngredient(val) {
      console.log(val);
      if (_.isObject(val)) {
        this.recipe =
          val.serving_qty + " " + val.serving_unit + " " + val.food_name;
        this.searchRecipe();
        this.recipe = "";
        /*
        this.ingredients.push({

          food_name: val.food_name,
          quantity: val.serving_qty,
          quantity_unit: units.base(units.type(val.serving_unit)),
          unit_type: units.type(val.serving_unit)
        });*/
      }
    },
    getIngredientList: function(ingredients) {
      let ingredientList = "";
      ingredients.forEach(function(ingredient) {
        ingredientList +=
          ingredient.food_name.charAt(0).toUpperCase() +
          ingredient.food_name.slice(1) +
          ", ";
      });
      return ingredientList;
    },
    getNutritionTotals: function(ingredients, normalize = true) {
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
            units.base(ingredient.unit_type)
          );
        }

        nutrition.calories +=
          (ingredient.nf_calories || ingredient.calories) * multiplier;
        nutrition.totalFat +=
          (ingredient.nf_total_fat || ingredient.totalFat) * multiplier;
        nutrition.satFat +=
          (ingredient.nf_saturated_fat || ingredient.satFat) * multiplier;
        nutrition.transFat +=
          (ingredient.nf_trans_fat || ingredient.transFat) * multiplier;
        nutrition.cholesterol +=
          (ingredient.nf_cholesterol || ingredient.cholesterol) * multiplier;
        nutrition.sodium +=
          (ingredient.nf_sodium || ingredient.sodium) * multiplier;
        nutrition.totalCarb +=
          (ingredient.nf_total_carbohydrate || ingredient.totalCarb) *
          multiplier;
        nutrition.fibers +=
          (ingredient.nf_dietary_fiber || ingredient.fibers) * multiplier;
        nutrition.sugars +=
          (ingredient.nf_sugars || ingredient.sugars) * multiplier;
        nutrition.proteins +=
          (ingredient.nf_protein || ingredient.proteins) * multiplier;
        nutrition.potassium +=
          (ingredient.nf_potassium || ingredient.potassium) * multiplier;
        nutrition.vitaminD +=
          (vitamindIndex > -1
            ? ingredient.full_nutrients[vitamindIndex].value
            : ingredient.vitaminD) * multiplier;
        nutrition.calcium +=
          (calciumIndex > -1
            ? ingredient.full_nutrients[calciumIndex].value
            : ingredient.calcium);
        nutrition.iron +=
          (ironIndex > -1
            ? ingredient.full_nutrients[ironIndex].value
            : ingredient.iron);
        nutrition.sugars +=
          (ingredient.nf_addedsugars || ingredient.sugars) * multiplier;
      });

      return nutrition;
    },
    getNutritionFacts(ingredients) {
      const nutrition = this.getNutritionTotals(ingredients);
      const ingredientList = this.getIngredientList(ingredients);

      $(this.$refs.nutritionFacts).html("");

      $(this.$refs.nutritionFacts).nutritionLabel({
        showServingUnitQuantity: false,
        itemName: this.meal.title,
        ingredientList: ingredientList,

        decimalPlacesForQuantityTextbox: 2,
        valueServingUnitQuantity: 1,

        allowFDARounding: true,
        decimalPlacesForNutrition: 2,

        showPolyFat: false,
        showMonoFat: false,

        valueCalories: nutrition.calories,
        valueFatCalories: nutrition.fatCalories,
        valueTotalFat: nutrition.totalFat,
        valueSatFat: nutrition.satFat,
        valueTransFat: nutrition.transFat,
        valueCholesterol: nutrition.cholesterol,
        valueSodium: nutrition.sodium,
        valueTotalCarb: nutrition.totalCarb,
        valueFibers: nutrition.fibers,
        valueSugars: nutrition.sugars,
        valueProteins: nutrition.proteins,
        valueVitaminD: (nutrition.vitaminD / 20000) * 100,
        valuePotassium_2018: (nutrition.potassium / 4700) * 100,
        valueCalcium: (nutrition.calcium / 1300) * 100,
        valueIron: (nutrition.iron / 18) * 100,
        valueAddedSugars: nutrition.addedSugars,
        showLegacyVersion: false
      });
    }
  }
};
</script>