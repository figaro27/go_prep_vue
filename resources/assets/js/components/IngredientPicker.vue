<template>
  <div>
    <b-form class="mb-2" @submit.prevent="searchRecipe">
      <div class="mb-2">
        <IngredientSearch @change="onSearchIngredient"/>
      </div>
      <div class="d-flex mb-2">
        <b-input v-model="recipe" class="flex-grow-1 mr-1" placeholder="Type Ingredients Here"></b-input>
        <b-button @click="searchRecipe" variant="primary">Search</b-button>
      </div>
      <div class="d-flex mb-2">
        <v-select
          class="flex-grow-1 mr-1"
          placeholder="Or search from your saved ingredients"
          :options="existingIngredientOptions"
          v-model="selectedExistingIngredients"
          multiple
        ></v-select>
        <b-button @click="onClickAddExistingIngredient" variant="primary">Add</b-button>
      </div>
    </b-form>
    <table class="table w-100 ingredients-table">
      <thead>
        <th>Name</th>
        <th>Weight</th>
        <th>Unit</th>
        <th style="width: 30px"></th>
      </thead>
      <tbody>
        <tr v-for="(ingredient, i) in ingredients" :key="ingredient.food_name">
          <td>
            <b-form-group>
              <v-select
                class="ingredient-dropdown"
                label="name"
                :filterable="false"
                :options="ingredientOptions"
                @search="onSearch"
                :value="ingredient"
                :onChange="(val) => onAddIngredient(val, i)"
              >
                <template slot="no-options">type to search ingredients...</template>
                <template slot="option" slot-scope="option">
                  <div class="d-center">
                    <img v-if="option.photo" :src="option.photo.thumb" class="thumb">
                    {{ option.food_name }}
                  </div>
                </template>
                <template slot="selected-option" slot-scope="option">
                  <div class="selected">
                    <img v-if="option.photo" :src="option.photo.thumb" class="thumb">
                    {{ option.food_name }}
                  </div>
                </template>
              </v-select>
            </b-form-group>
          </td>
          <td>
            <b-form-group>
              <b-form-input placeholder="Weight" :value="ingredient.quantity"></b-form-input>
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
              <span v-else>Unit</span>
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
              <b-col class="text-right">
                <a href="#" @click.prevent="onClickAddIngredient">
                  <i class="fas fa-plus-circle"></i>
                </a>
              </b-col>
            </b-row>
          </td>
        </tr>
      </tbody>
    </table>
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
}
.ingredient-table {
  th, td {
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
  //components: [],
  props: {
    value: {},
    options: {
      default: {
        saveButton: false
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
          label: ingredient.food_name
        };
      });
    },
    unitOptions: () => ingredient => {
      let type = ingredient.unit_type;
      return units[type].selectOptions();
    },
    canSave() {
      return this.ingredients.length > 0;
    }
  },
  watch: {
    ingredients(newIngredients, oldIngredients) {
      if (!_.isEqual(newIngredients, oldIngredients)) {
        this.update();
      }
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
        this.newIngredients.push(ingredient.value);
      });

      this.selectedExistingIngredients = [];
    },
    onAddIngredient(val, i) {
      if (val) {
        if (!val.unit_type) {
          val.unit_type = units.type(units.normalize(val.serving_unit));
        }
        if (!val.quantity) {
          val.quantity = 1;
        }
        if (!val.quantity_unit) {
          val.quantity_unit = units.normalize(val.serving_unit);
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
    },
    searchInstant: function() {},
    searchRecipe: function() {
      axios
        .post("/nutrients", {
          query: this.recipe
        })
        .then(response => {
          let newIngredients = _.map(response.data.foods, ingredient => {
            // Get properly named unit
            let unit = units.normalize(ingredient.serving_unit);
            let measure = units.describe(unit);

            ingredient.unit_type = units.type(unit);
            ingredient.quantity = ingredient.serving_qty;
            ingredient.quantity_unit = unit;
            return ingredient;
          });
          this.ingredients = _.concat(this.ingredients, newIngredients);
          this.recipe = "";
        });
    },
    onSearch(search, loading) {
      loading(true);
      this.search(loading, search, this);
    },
    search: _.debounce((loading, search, vm) => {
      axios
        .post("../searchInstant", {
          search: search
        })
        .then(response => {
          vm.ingredientOptions = response.data.common;
          loading(false);
        });
    }, 350),
    onSearchIngredient(val) {
      if (_.isObject(val)) {
        this.ingredients.push({
          food_name: val.food_name,
          quantity: 1,
          quantity_unit: units.base(units.type(val.serving_unit)),
          unit_type: units.type(val.serving_unit)
        });
      }
    }
  }
};
</script>