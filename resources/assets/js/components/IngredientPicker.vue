<template>
  <div>
    <b-form @submit.prevent="saveMealServings">
      <div class="row mb-3 mt-2">
        <div class="col-md-3">
          <strong>Servings Per Container</strong>
          <b-form-input
            required
            v-model="meal.servingsPerMeal"
            placeholder="1"
            v-if="!componentAddonPage"
            type="number"
            min="0"
          ></b-form-input>
          <span v-if="componentAddonPage" class="strong"
            >: {{ meal.servingsPerMeal }}</span
          >
        </div>
        <div class="col-md-3">
          <strong>Serving Size Unit</strong>
          <b-form-input
            v-model="meal.servingSizeUnit"
            placeholder="Protein Pancake"
            v-if="!componentAddonPage"
          ></b-form-input>
          <span v-if="componentAddonPage" class="strong"
            >: {{ meal.servingSizeUnit }}</span
          >
        </div>
        <div class="col-md-3">
          <strong>Serving Size Quantity</strong>
          <b-form-input
            type="number"
            min="1"
            v-model="meal.servingUnitQuantity"
            placeholder="1"
            v-if="!componentAddonPage"
          ></b-form-input>
          <span v-if="componentAddonPage" class="strong"
            >: {{ meal.servingSizeUnit }}</span
          >
        </div>
        <div class="col-md-3">
          <b-btn
            variant="primary"
            class="mt-4"
            type="submit"
            v-if="!componentAddonPage && !createMealModal"
            >Save</b-btn
          >
        </div>
      </div>
    </b-form>
    <img src="/images/nutritionix.png" class="nutritionix mb-3" />
    <b-form class="mb-2" @submit.prevent="searchRecipe">
      <b-tabs class="mb-2">
        <b-tab title="Type in Ingredients" active>
          <b-form-textarea
            required
            v-model="recipe"
            class="flex-grow-1 mr-1 mb-1"
            :rows="3"
            placeholder="Enter ingredients that make up the meal like '3 oz chicken breast, 1/2 tsp salt, etc'. Include measurements such as tsp, tbsp, cup, gram, oz, fl oz, and separate the ingredients by comma."
          ></b-form-textarea>
          <b-button type="submit" variant="primary">Add</b-button>
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
              :searchable="true"
            ></v-select>
            <b-button
              @click="onClickAddExistingIngredient"
              variant="primary"
              class="flex-grow-0"
              >Add</b-button
            >
          </div>
        </b-tab>
        <b-tab title="Add Custom Ingredient">
          <b-form @submit.prevent="addToRecipe(false)">
            <div class="row">
              <div class="col-md-6">
                <b-form-input
                  type="text"
                  v-model="customIngredient.food_name"
                  placeholder="Ingredient Name"
                  required
                ></b-form-input>
              </div>
              <div class="col-md-3">
                <b-form-input
                  type="number"
                  v-model="customIngredient.serving_qty"
                  placeholder="Weight"
                  required
                ></b-form-input>
              </div>
              <div class="col-md-3">
                <b-form-select
                  v-model="customIngredient.serving_unit"
                  :options="allUnitOptions"
                ></b-form-select>
              </div>
            </div>
            <hr />
            <b-form-group>
              <div class="row">
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    v-model="customIngredient.calories"
                    placeholder="Calories"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    v-model="customIngredient.cholesterol"
                    placeholder="Cholesterol"
                    class="mt-2"
                  ></b-form-input>
                  <!-- <b-form-input
                    type="number"
                    v-model="customIngredient.vitamind"
                    placeholder="Vitamin D"
                    class="mt-2"
                  ></b-form-input> -->
                </div>
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    v-model="customIngredient.totalfat"
                    placeholder="Total Fat"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    v-model="customIngredient.satfat"
                    placeholder="Saturated Fat"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    v-model="customIngredient.transfat"
                    placeholder="Trans Fat"
                    class="mt-2"
                  ></b-form-input>
                </div>
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    v-model="customIngredient.totalcarb"
                    placeholder="Total Carb"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    v-model="customIngredient.sugars"
                    placeholder="Sugars"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    v-model="customIngredient.sodium"
                    placeholder="Sodium"
                    class="mt-2"
                  ></b-form-input>
                </div>
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    v-model="customIngredient.proteins"
                    placeholder="Proteins"
                    class="mt-2"
                  ></b-form-input>
                  <!-- <b-form-input
                    type="number"
                    v-model="customIngredient.potassium"
                    placeholder="Potassium"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    v-model="customIngredient.calcium"
                    placeholder="Calcium"
                    class="mt-2"
                  ></b-form-input> -->
                </div>
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    v-model="customIngredient.fibers"
                    placeholder="Fibers"
                    class="mt-2"
                  ></b-form-input>
                  <!-- <b-form-input
                    type="number"
                    v-model="customIngredient.iron"
                    placeholder="Iron"
                    class="mt-2"
                  ></b-form-input> -->
                </div>
              </div>
            </b-form-group>
            <b-button type="submit" variant="primary">Add</b-button>
          </b-form>
        </b-tab>

        <b-tab title="Enter Nutrition Directly">
          <b-form @submit.prevent="addToRecipe(true)">
            <hr />
            <b-form-group>
              <div class="row">
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.calories"
                    placeholder="Calories"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.cholesterol"
                    placeholder="Cholesterol"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.vitamind"
                    placeholder="Vitamin D"
                    class="mt-2"
                  ></b-form-input>
                </div>
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.totalfat"
                    placeholder="Total Fat"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.satfat"
                    placeholder="Saturated Fat"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.transfat"
                    placeholder="Trans Fat"
                    class="mt-2"
                  ></b-form-input>
                </div>
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.totalcarb"
                    placeholder="Total Carb"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.sugars"
                    placeholder="Sugars"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.sodium"
                    placeholder="Sodium"
                    class="mt-2"
                  ></b-form-input>
                </div>
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.proteins"
                    placeholder="Proteins"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.potassium"
                    placeholder="Potassium"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.calcium"
                    placeholder="Calcium"
                    class="mt-2"
                  ></b-form-input>
                </div>
                <div class="col-md-2">
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.fibers"
                    placeholder="Fibers"
                    class="mt-2"
                  ></b-form-input>
                  <b-form-input
                    type="number"
                    min="0"
                    v-model="customIngredient.iron"
                    placeholder="Iron"
                    class="mt-2"
                  ></b-form-input>
                </div>
              </div>
            </b-form-group>
            <b-button type="submit" variant="primary">Add</b-button>
          </b-form>
        </b-tab>
      </b-tabs>
    </b-form>

    <div class="d-flex">
      <table class="table w-100 ingredients-table">
        <table>
          <thead>
            <th>Name</th>
            <th>Weight</th>
            <th>Units</th>
            <th style="width: 10px"></th>
          </thead>
        </table>
        <table
          v-for="(ingredient, i) in ingredients"
          :key="ingredient.food_name"
        >
          <tr>
            <td>
              <img
                :src="ingredient.image_thumb"
                v-if="ingredient.image_thumb"
                class="ingredient-thumb"
              />
              {{ ingredient.food_name }}
            </td>
            <td>
              <b-form-group>
                <b-form-input
                  style="width:75px"
                  v-if="!ingredient.hidden"
                  placeholder="Weight"
                  v-model="ingredient.quantity"
                  type="number"
                  :formatter="
                    (val, e) =>
                      typeof val === 'string'
                        ? val.replace(/[^\d.-]/g, '')
                        : val
                  "
                ></b-form-input>
              </b-form-group>
            </td>
            <td class="text-center">
              <b-form-group>
                <b-select
                  v-if="ingredient.unit_type !== 'unit' && !ingredient.hidden"
                  v-model="ingredient.quantity_unit"
                  :options="unitOptions(ingredient)"
                  style="width: 60px"
                >
                  <option slot="top" disabled>-- Select unit --</option>
                </b-select>
                <span v-else
                  ><p v-if="!ingredient.hidden">
                    {{ ingredient.quantity_unit_display }}
                  </p></span
                >
              </b-form-group>
            </td>
            <td>
              <b-btn variant="link" @click="removeIngredient(i)">
                <i class="fa fa-close"></i>
              </b-btn>
            </td>
          </tr>
          <tr class="nutrientsTable">
            <td>Calories</td>
            <td>Protein</td>
            <td>Carbs</td>
            <td>Fat</td>
            <td>Sugars</td>
            <td>Sodium</td>
            <td>Cholesterol</td>
          </tr>
          <tr class="nutrientsTable">
            <td>{{ processSingleIngredient(ingredient, "calories") }}</td>
            <td>{{ processSingleIngredient(ingredient, "proteins") }}</td>
            <td>{{ processSingleIngredient(ingredient, "totalcarb") }}</td>
            <td>{{ processSingleIngredient(ingredient, "totalfat") }}</td>
            <td>{{ processSingleIngredient(ingredient, "sugars") }}</td>
            <td>{{ processSingleIngredient(ingredient, "sodium") }}</td>
            <td>{{ processSingleIngredient(ingredient, "cholesterol") }}</td>
          </tr>
        </table>

        <tr>
          <td colspan="10" class="text-right">
            <b-row>
              <b-col v-if="options.saveButton" class="text-left">
                <b-button
                  variant="primary"
                  :disabled="!canSave"
                  @click.prevent="save"
                  >Save</b-button
                >
              </b-col>
              <!-- <b-col class="text-right">
                  <a href="#" @click.prevent="onClickAddIngredient">
                    <i class="fas fa-plus-circle"></i>
                  </a>
                </b-col>-->
            </b-row>
          </td>
        </tr>
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
import nutrition from "../data/nutrition";
import format from "../lib/format";

export default {
  props: {
    createMealModal: false,
    componentAddonPage: false,
    componentTitle: null,
    componentOptionTitle: null,
    addonTitle: null,
    mealSizeId: null,
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
      customIngredient: {
        serving_unit: "g",
        image: "http://goprep.com/images/defaultIngredient.jpg",
        image_thumb: "http://goprep.com/images/defaultIngredient.jpg"
      },
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
      defaultWeightUnit: "defaultWeightUnit",
      store: "viewedStore"
    }),
    nutrition() {
      return nutrition;
    },
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
    allUnitOptions() {
      const massOptions = units["mass"].selectOptions();
      const volumeOptions = units["volume"].selectOptions();
      const allOptions = _.concat(massOptions, volumeOptions);
      allOptions.push("Units");
      return allOptions;
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
    this.ingredients = this.ingredients.sort((a, b) => {
      return b.pivot.quantity_base - a.pivot.quantity_base;
    });
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
        let totals = nutrition.getTotals([val], false);
        if (totals) {
          totals = _.mapValues(totals, prop => {
            return prop * multiplier;
          });
          val = _.merge(val, totals);
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
      this.$toastr.s("Ingredients saved.");
    },
    searchInstant: function() {},
    processSingleIngredient(ingredient, macro) {
      let nutrient = null;
      switch (macro) {
        case "calories":
          nutrient = ingredient.calories;
          break;
        case "proteins":
          nutrient = ingredient.proteins;
          break;
        case "totalcarb":
          nutrient = ingredient.totalcarb;
          break;
        case "totalfat":
          nutrient = ingredient.totalfat;
          break;
        case "sugars":
          nutrient = ingredient.sugars;
          break;
        case "sodium":
          nutrient = ingredient.sodium;
          break;
        case "cholesterol":
          nutrient = ingredient.cholesterol;
          break;
      }
      let baseUnit = "ml";
      if (
        ingredient.quantity_unit === "mg" ||
        ingredient.quantity_unit === "g" ||
        ingredient.quantity_unit === "oz" ||
        ingredient.quantity_unit === "lb" ||
        ingredient.quantity_unit === "kg"
      ) {
        baseUnit = "g";
      }
      let mac = (
        units.convert(nutrient, ingredient.quantity_unit, baseUnit, true) *
        ingredient.quantity
      ).toFixed(1);

      return !isNaN(mac) ? mac : 0;
      // if (!isNaN(mac)){
      //   return mac;
      // }
      // else {
      //   return 0;
      // }
    },
    processFoods(foods) {
      return _.map(foods, ingredient => {
        // Get properly named unit
        if (ingredient.serving_unit === "lbs") ingredient.serving_unit = "lb";
        if (ingredient.serving_unit === "kgs") ingredient.serving_unit = "kg";

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
        let totals = nutrition.getTotals([ingredient], false);
        if (totals) {
          totals = _.mapValues(totals, prop => {
            return prop * multiplier;
          });
          ingredient = _.merge(ingredient, totals);
        }

        return ingredient;
      });
    },
    processCustomIngredient(ingredient) {
      let unit = units.normalize(ingredient.serving_unit);
      ingredient.unit_type = units.type(unit);
      ingredient.quantity_unit_display = unit;

      if (ingredient.unit_type === "unit") {
        unit = "unit";
      }

      ingredient.quantity = ingredient.serving_qty;
      ingredient.quantity_unit = unit;
      ingredient.added = true;

      let multiplier =
        units.convert(1, units.base(ingredient.unit_type), unit, false) /
        ingredient.quantity;

      let totals = nutrition.getTotals([ingredient], false);
      if (totals) {
        totals = _.mapValues(totals, prop => {
          return prop * multiplier;
        });
        ingredient = _.merge(ingredient, totals);
      }

      return ingredient;
    },
    addToRecipe(directNutrition) {
      if (!directNutrition) {
        let customIngr = this.processCustomIngredient(this.customIngredient);
        this.ingredients = _.concat(this.ingredients, customIngr);
        this.$toastr.s("Ingredient Added");
      } else {
        // Naming the ingredient the full meal with variation title.
        let baseTitle = this.meal.full_title
          ? this.meal.full_title
          : this.meal.title;
        let componentTitle =
          this.componentTitle !== undefined
            ? " - C: " + this.componentTitle + " - " + this.componentOptionTitle
            : "";
        let addonTitle =
          this.addonTitle !== undefined ? " - A: " + this.addonTitle : "";
        let finalTitle =
          baseTitle.concat(componentTitle + addonTitle) +
          " ID - " +
          Math.random()
            .toString(18)
            .substring(2, 6)
            .toUpperCase() +
          Math.random()
            .toString(36)
            .substring(2, 6)
            .toUpperCase();

        this.customIngredient.food_name = finalTitle;
        this.customIngredient.serving_qty = 1;
        this.customIngredient.serving_unit = "g";
        this.customIngredient.hidden = 1;
        let customIngr = this.processCustomIngredient(this.customIngredient);
        this.ingredients = _.concat(this.ingredients, customIngr);
        this.$toastr.s("Nutrition Added");
      }
      this.customIngredient = {
        serving_unit: "g",
        image: "http://goprep.com/images/defaultIngredient.jpg",
        image_thumb: "http://goprep.com/images/defaultIngredient.jpg"
      };
    },
    searchRecipe() {
      axios
        .post("/api/nutrients", {
          query: this.recipe,
          storeId: this.store.id
        })
        .then(response => {
          let newIngredients = this.processFoods(response.data.foods);
          this.ingredients = _.concat(this.ingredients, newIngredients);
          this.recipe = "";
        })
        .catch(e => {
          this.$toastr.w("No ingredients found.", "Sorry!");
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
      if (_.isObject(val)) {
        if (val.nix_item_id) {
          axios
            .post("/api/nutrients/" + val.nix_item_id, {
              query: this.recipe
            })
            .then(response => {
              let newIngredients = this.processFoods(response.data.foods);
              this.ingredients = _.concat(this.ingredients, newIngredients);
            });
        } else {
          let ingredientName = val.food_name;

          this.recipe =
            val.serving_qty + " " + val.serving_unit + " " + ingredientName;
          this.searchRecipe();
          this.recipe = "";
        }
      }
    },
    getNutritionFacts(ingredients) {
      const nutrition = this.nutrition.getTotals(ingredients);
      const ingredientList = this.nutrition.getIngredientList(ingredients);
      const servingsPerMeal = this.meal.servingsPerMeal
        ? this.meal.servingsPerMeal
        : 1;
      const servingUnitQuantity = this.meal.servingUnitQuantity
        ? this.meal.servingUnitQuantity
        : 1;
      const servingSizeUnit = this.meal.servingSizeUnit
        ? this.meal.servingSizeUnit
        : "";

      $(this.$refs.nutritionFacts).html("");

      $(this.$refs.nutritionFacts).nutritionLabel({
        showItemName: false,
        showServingUnitQuantity: true,
        valueServingPerContainer: servingsPerMeal,
        valueServingUnitQuantity: servingUnitQuantity,
        valueServingSizeUnit: servingSizeUnit,
        showServingsPerContainer: true,
        showPolyFat: false,
        showMonoFat: false,
        showTransFat: false,
        showFibers: true,
        showVitaminD: false,
        showPotassium_2018: false,
        showCalcium: false,
        showIron: false,
        showCaffeine: false,
        itemName: this.meal.title ? this.meal.title : "",
        ingredientList: ingredientList,
        showIngredients: this.showIngredients,
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
        // ((nutrition.potassium / 4700) * 100) / servingsPerMeal,
        // valueCalcium: ((nutrition.calcium / 1300) * 100) / servingsPerMeal,
        // valueIron: ((nutrition.iron / 18) * 100) / servingsPerMeal,
        valueAddedSugars: nutrition.addedSugars / servingsPerMeal,
        showLegacyVersion: false
      });
    },
    saveMealServings() {
      if (this.mealSizeId >= 1000000) {
        // If the meal size hasn't been saved yet, grab latest meal size ID.
        this.saveMealServingsNewSize();
      } else {
        axios
          .post("/api/me/saveMealServings", {
            id: this.$parent.meal ? this.$parent.meal.id : this.meal.id,
            meal_size_id: this.mealSizeId,
            servingsPerMeal: this.meal.servingsPerMeal,
            servingUnitQuantity: this.meal.servingUnitQuantity,
            servingSizeUnit: this.meal.servingSizeUnit
          })
          .then(resp => {
            this.$toastr.s("Meal serving info saved.");
            this.getNutritionFacts(this.ingredients);
          });
      }
    },
    saveMealServingsNewSize() {
      axios
        .post("/api/me/getLatestMealSize", { id: this.$parent.meal.id })
        .then(resp => {
          axios
            .post("/api/me/saveMealServings", {
              id: this.$parent.meal ? this.$parent.meal.id : this.meal.id,
              meal_size_id: resp.data,
              servingsPerMeal: this.meal.servingsPerMeal,
              servingSizeUnit: this.meal.servingSizeUnit
            })
            .then(resp => {
              this.$toastr.s("Meal serving info saved.");
              this.getNutritionFacts(this.ingredients);
            });
        });
    }
  }
};
</script>
<style>
.nutrientsTable td {
  width: 100px !important;
  text-align: center !important;
}
</style>
