<template>
  <div>
    <img src="/images/nutritionix.png" class="nutritionix mb-3" />
    <b-form class="mb-2" @submit.prevent="searchRecipe">
      <b-tabs class="mb-2">
        <b-tab title="Type in Ingredients" active>
          <b-form-textarea
            v-model="recipe"
            class="flex-grow-1 mr-1 mb-1"
            :rows="3"
            placeholder="Enter a query like '1 cup mashed potatoes and 2 tbsp gravy'. Be sure to include accurate measurement names such as tsp, tbsp, cup, gram, oz, fl oz, etc.."
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
          <tr
            v-for="(ingredient, i) in ingredients"
            :key="ingredient.food_name"
          >
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
                  placeholder="Weight"
                  v-model="ingredient.quantity"
                  :formatter="(val, e) => val.replace(/[^\d.-]/g, '')"
                ></b-form-input>
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
                <span v-else>{{ ingredient.quantity_unit_display }}</span>
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
        </tbody>
      </table>

      <div class="ml-5" ref="nutritionFacts"></div>
    </div>
    <h3>Add Custom Ingredient</h3>
    <b-form @submit.prevent="addCustomIngredient">
      <b-form-group>
        <b-form-input
          type="string"
          v-model="customIngredient.food_name"
          placeholder="Food Name"
          required
        ></b-form-input>
      </b-form-group>
      <b-form-group>
        <b-form-input
          type="int"
          v-model="customIngredient.quantity"
          placeholder="Quantity"
          required
        ></b-form-input>
      </b-form-group>
      <b-form-group label="Unit Type">
        <b-form-select
          v-model="customIngredient.quantity_unit"
          :options="allUnitOptions"
        ></b-form-select>
      </b-form-group>
      <b-form-group label="Nutrition">
        <b-form-input
          type="int"
          v-model="customIngredient.calories"
          placeholder="Calories"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.totalfat"
          placeholder="Total Fat"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.satfat"
          placeholder="Saturated Fat"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.transfat"
          placeholder="Trans Fat"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.cholesterol"
          placeholder="Cholesterol"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.sodium"
          placeholder="Sodium"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.totalcarb"
          placeholder="Total Carb"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.fibers"
          placeholder="Fibers"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.sugars"
          placeholder="Sugars"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.proteins"
          placeholder="Proteins"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.vitamind"
          placeholder="Vitamin D"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.potassium"
          placeholder="Potassium"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.calcium"
          placeholder="Calcium"
          required
        ></b-form-input>
        <b-form-input
          type="int"
          v-model="customIngredient.iron"
          placeholder="Iron"
          required
        ></b-form-input>
      </b-form-group>
    </b-form>
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
      customIngredient: {},
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
      this.$toastr.s("Ingredients saved!");
    },
    searchInstant: function() {},
    processFoods(foods) {
      return _.map(foods, ingredient => {
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
    searchRecipe() {
      axios
        .post("/api/nutrients", {
          query: this.recipe
        })
        .then(response => {
          let newIngredients = this.processFoods(response.data.foods);
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
